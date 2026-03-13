const fs = require('fs');
const files = [
    'resources/views/handai-pos/checkout/checkout-pos.blade.php',
    'resources/views/handai-pos/dashboard/index.blade.php',
    'resources/views/customer-order/checkout.blade.php',
    'resources/views/handai-kasir/checkout/checkout-kasir.blade.php'
];

files.forEach(f => {
    if (fs.existsSync(f)) {
        let content = fs.readFileSync(f, 'utf8');
        content = content.replace(/fetch\(\s*['"]\{\{\s*route\(\s*['"]([^'"]+)['"]\s*\)\s*\}\}['"]/g, 'fetch(\'{{ route("$1", [], false) }}\'');
        fs.writeFileSync(f, content);
    }
});
console.log('Fixed fetch routes');