<?php

namespace App\Services;

use App\Models\Setting;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use App\Traits\NeoFeeder\HandlesReferensi;
use App\Traits\NeoFeeder\HandlesMahasiswa;
use App\Traits\NeoFeeder\HandlesAkademik;
use App\Traits\NeoFeeder\HandlesDosen;

class NeoFeederService
{
    use HandlesReferensi, HandlesMahasiswa, HandlesAkademik, HandlesDosen;

    private Client $client;
    private Client $quickClient;
    private string $url;
    private string $username;
    private string $password;
    private ?string $token = null;
    private int $maxRetries = 3;
    private int $retryDelay = 2; // seconds

    public function __construct()
    {
        // Get credentials from database (encrypted password auto-decrypted)
        $this->url = Setting::getValue('neo_feeder_url', '');
        $this->username = Setting::getValue('neo_feeder_username', '');
        $this->password = Setting::getValue('neo_feeder_password', '');

        $this->client = new Client([
            'timeout' => 200, // Increased to 200s to handle slow responses (batch 500)
            'connect_timeout' => 30,
            'verify' => false,
        ]);

        // Quick client for GetCount* endpoints that may hang
        $this->quickClient = new Client([
            'timeout' => 30, // Increased to 30s
            'connect_timeout' => 10,
            'verify' => false,
        ]);
    }

    /**
     * Get authentication token from Neo Feeder
     */
    public function getToken(): ?string
    {
        if ($this->token) {
            return $this->token;
        }

        try {
            $response = $this->client->post($this->url, [
                'json' => [
                    'act' => 'GetToken',
                    'username' => $this->username,
                    'password' => $this->password,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['data']['token'])) {
                $this->token = $data['data']['token'];
                return $this->token;
            }

            Log::error('Neo Feeder GetToken failed', ['response' => $data]);
            return null;
        } catch (GuzzleException $e) {
            Log::error('Neo Feeder GetToken error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Quick request with short timeout for non-critical calls (e.g. GetCount*)
     * No retries - returns null on failure instead of blocking for minutes.
     */
    public function requestQuick(string $action, array $params = []): ?array
    {
        $token = $this->getToken();
        if (!$token) {
            return null;
        }

        try {
            Log::info("Neo Feeder Quick Request: {$action}", ['params' => $params]);
            $response = $this->quickClient->post($this->url, [
                'json' => array_merge([
                    'act' => $action,
                    'token' => $token,
                ], $params),
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
            return $data;
        } catch (\Exception $e) {
            Log::warning("Neo Feeder {$action} quick request failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Make API request to Neo Feeder with retry logic
     */
    public function request(string $action, array $params = []): ?array
    {
        $token = $this->getToken();
        if (!$token) {
            return null;
        }

        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
                // Add small delay between requests to prevent overwhelming the server
                if ($attempt > 0) {
                    $delay = $this->retryDelay * pow(2, $attempt - 1); // Exponential backoff
                    Log::info("Neo Feeder {$action} retry #{$attempt}, waiting {$delay}s");
                    sleep($delay);
                }

                Log::info("Neo Feeder Request: {$action}", ['params' => $params]);

                $response = $this->client->post($this->url, [
                    'json' => array_merge([
                        'act' => $action,
                        'token' => $token,
                    ], $params),
                ]);

                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                if (is_null($data)) {
                    Log::warning("Neo Feeder {$action} returned non-JSON response", ['body' => substr($body, 0, 2000)]);
                    // Return raw decoded data (null) so callers can handle it
                    return null;
                }

                // If API reports an error related to token (expired/invalid), refresh token and retry once
                if (isset($data['error_code']) && $data['error_code'] != 0) {
                    $desc = strtolower($data['error_desc'] ?? '');
                    if (str_contains($desc, 'token') || str_contains($desc, 'expired') || str_contains($desc, 'invalid')) {
                        Log::info("Neo Feeder {$action} detected token issue; refreshing token and retrying");
                        // Clear cached token and obtain a new one
                        $this->token = null;
                        $token = $this->getToken();
                        if (!$token) {
                            return null;
                        }
                        $attempt++;
                        continue;
                    }
                }

                return $data;
            } catch (ConnectException $e) {
                // Timeout or connection error - retry
                $attempt++;
                $lastException = $e;
                Log::warning("Neo Feeder {$action} timeout (attempt {$attempt}/{$this->maxRetries})", [
                    'message' => $e->getMessage()
                ]);
            } catch (RequestException $e) {
                // HTTP errors - retry for server-side issues (5xx) and rate limits (429)
                $status = null;
                if (method_exists($e, 'getResponse') && $e->getResponse()) {
                    $status = $e->getResponse()->getStatusCode();
                }

                if ($status === 429 || ($status >= 500 && $status < 600)) {
                    $attempt++;
                    $lastException = $e;
                    Log::warning("Neo Feeder {$action} server error (status {$status}) - retrying (attempt {$attempt}/{$this->maxRetries})", ['message' => $e->getMessage()]);
                    continue;
                }

                Log::error("Neo Feeder {$action} request error", ['message' => $e->getMessage(), 'status' => $status]);
                return null;
            } catch (GuzzleException $e) {
                // Other errors - don't retry
                Log::error("Neo Feeder {$action} error", ['message' => $e->getMessage()]);
                return null;
            }
        }

        // All retries exhausted
        Log::error("Neo Feeder {$action} failed after {$this->maxRetries} retries", [
            'message' => $lastException?->getMessage()
        ]);
        return null;
    }
}
