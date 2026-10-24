import fs from 'fs';
import path from 'path';
import {fileURLToPath} from 'url';
const ROOT=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');
for(const rel of ['assets/app.js','assets/app.css']){
  const p=path.join(ROOT,rel);
  const s=fs.readFileSync(p).toString('utf8').replace(/^﻿/,'');
  fs.writeFileSync(p,'﻿'+s,'utf8');
  console.log('BOM',rel,fs.statSync(p).size);
}
