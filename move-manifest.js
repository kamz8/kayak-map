import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const sourceDir = path.join(__dirname, 'public', 'build', '.vite');
const targetDir = path.join(__dirname, 'public', 'build');
const manifestFile = 'manifest.json';

try {
    // Sprawdź, czy plik istnieje w źródłowym katalogu
    if (fs.existsSync(path.join(sourceDir, manifestFile))) {
        // Przenieś plik
        fs.renameSync(
            path.join(sourceDir, manifestFile),
            path.join(targetDir, manifestFile)
        );
        console.log(`Plik ${manifestFile} został pomyślnie przeniesiony.`);
    } else {
        console.log(`Plik ${manifestFile} nie istnieje w katalogu źródłowym.`);
    }
} catch (err) {
    console.error('Wystąpił błąd podczas przenoszenia pliku:', err);
}
