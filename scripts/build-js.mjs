// Copia o build pronto do Alpine.js para public/js — evita depender de CDN
// em runtime (nada de script de terceiros carregado pelo navegador do
// usuário final; o arquivo vem do node_modules já auditado pelo npm).
import { copyFileSync, mkdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const rootDir = dirname(dirname(fileURLToPath(import.meta.url)));
const source = join(rootDir, 'node_modules', 'alpinejs', 'dist', 'cdn.min.js');
const destDir = join(rootDir, 'public', 'js');
const dest = join(destDir, 'alpine.min.js');

mkdirSync(destDir, { recursive: true });
copyFileSync(source, dest);

console.log(`alpine.min.js copiado para ${dest}`);
