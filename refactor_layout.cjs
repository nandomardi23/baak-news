const fs = require('fs');
const path = require('path');

const walk = function(dir, done) {
    let results = [];
    fs.readdir(dir, function(err, list) {
        if (err) return done(err);
        let pending = list.length;
        if (!pending) return done(null, results);
        list.forEach(function(file) {
            file = path.resolve(dir, file);
            fs.stat(file, function(err, stat) {
                if (stat && stat.isDirectory()) {
                    walk(file, function(err, res) {
                        results = results.concat(res);
                        if (!--pending) done(null, results);
                    });
                } else {
                    if (file.endsWith('.vue')) {
                        results.push(file);
                    }
                    if (!--pending) done(null, results);
                }
            });
        });
    });
};

walk(path.join(__dirname, 'resources/js/pages'), function(err, results) {
    if (err) throw err;
    let modifiedCount = 0;
    
    results.forEach(file => {
        let content = fs.readFileSync(file, 'utf8');
        
        // Skip if already modified or doesn't use AppLayout
        if (!content.includes('import AppLayout') || content.includes('defineOptions({ layout: AppLayout })')) {
            return;
        }

        let isModified = false;

        // 1. Add defineOptions and useBreadcrumbs import
        if (content.match(/<script setup[^>]*>/)) {
            // Find where to insert defineOptions (right after script setup)
            content = content.replace(/(<script setup[^>]*>\n)/, `$1import { useBreadcrumbs } from '@/composables/useBreadcrumbs';\n\ndefineOptions({ layout: AppLayout });\nconst { setBreadcrumbs } = useBreadcrumbs();\n`);
            isModified = true;
        }

        // 2. Wrap existing breadcrumbs with setBreadcrumbs
        if (content.includes('const breadcrumbs')) {
            content = content.replace(/const breadcrumbs(?::\s*BreadcrumbItem\[\])?\s*=\s*(\[[\s\S]*?\]);/g, 'setBreadcrumbs($1);');
        }

        // 3. Keep layout wrapper logic: replace <AppLayout :breadcrumbs="breadcrumbs"> or similar
        // with just a standard <div> with same classes if needed, or remove it.
        // Usually AppLayout has a single child or we can just remove the tag.
        // Wait, some have <AppLayout :breadcrumbs="breadcrumbs"> and some have <AppLayout>
        // We'll replace the opening tag with nothing or a functional div.
        // Note: The new AppLayout has no wrappers in the page. Before, the page's whole content was inside <AppLayout>
        
        let startTagMatch = content.match(/<AppLayout[^>]*>/);
        if (startTagMatch) {
            content = content.replace(startTagMatch[0], ''); // Remove opening tag
            // The closing tag might be nested deep.
            // Replace the last </AppLayout>
            let lastIndex = content.lastIndexOf('</AppLayout>');
            if (lastIndex !== -1) {
                content = content.substring(0, lastIndex) + content.substring(lastIndex + '</AppLayout>'.length);
            }
            isModified = true;
        }

        if (isModified) {
            fs.writeFileSync(file, content, 'utf8');
            console.log('Modified:', file);
            modifiedCount++;
        }
    });

    console.log(`Refactored ${modifiedCount} files.`);
});
