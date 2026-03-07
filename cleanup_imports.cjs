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
        let isModified = false;

        // Strip "import { type BreadcrumbItem } from '@/types';"
        // Strip "import type { BreadcrumbItem } from '@/types';"
        // Strip "import { type BreadcrumbItemType } from '@/types';"
        const importRegex = /import\s+type\s+\{\s*BreadcrumbItem(?:Type)?\s*\}\s+from\s+['"]@\/types['"];?/g;
        const importRegex2 = /import\s+\{\s*type\s+BreadcrumbItem(?:Type)?\s*\}\s+from\s+['"]@\/types['"];?/g;
        
        if (content.match(importRegex) || content.match(importRegex2)) {
            content = content.replace(importRegex, '');
            content = content.replace(importRegex2, '');
            isModified = true;
        }

        if (isModified) {
            fs.writeFileSync(file, content, 'utf8');
            modifiedCount++;
        }
    });

    console.log(`Cleaned imports in ${modifiedCount} files.`);
});
