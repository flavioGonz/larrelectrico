import fs from 'fs';
const d=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));
const p0=d.products[0]||{};
console.log('count',d.count,'| vendors',(d.vendors||[]).length,'| p0 has docs field:',('docs' in p0),'| p0 has vendor field:',('vendor' in p0));
