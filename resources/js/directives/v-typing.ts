interface TypingOptions {
    speed?: number;          // Szybkość pisania (ms)
    startDelay?: number;     // Opóźnienie startu (ms)
    cursor?: boolean;        // Czy pokazywać kursor
    cursorChar?: string;     // Znak kursora
    cursorColor?: string;    // Kolor kursora
    loop?: boolean;          // Czy zapętlać animację
    eraseSpeed?: number;     // Szybkość usuwania (ms)
    eraseDelay?: number;     // Opóźnienie przed usuwaniem (ms)
    onComplete?: () => void; // Callback po zakończeniu
    preserveWhitespace?: boolean; // Czy zachować białe znaki
}

export const vTyping = {
    mounted(el: HTMLElement, binding: { value?: TypingOptions }) {
        // Domyślne opcje
        const options: TypingOptions = {
            speed: 70,
            startDelay: 0,
            cursor: true,
            cursorChar: '|',
            cursorColor: 'currentColor',
            loop: false,
            eraseSpeed: 50,
            eraseDelay: 2000,
            preserveWhitespace: true,
            ...binding.value
        };

        // Zapisz oryginalny tekst i style
        const originalText = options.preserveWhitespace
            ? el.innerHTML
            : el.textContent?.trim() || '';
        const originalDisplay = window.getComputedStyle(el).display;

        // Ustaw style dla kontenera
        el.style.display = originalDisplay === 'inline' ? 'inline-block' : originalDisplay;

        // Stwórz elementy dla tekstu i kursora
        const textSpan = document.createElement('span');
        const cursorSpan = document.createElement('span');

        // Dodaj style dla kursora
        if (options.cursor) {
            cursorSpan.textContent = options.cursorChar;
            cursorSpan.style.color = options.cursorColor;
            cursorSpan.style.animation = 'blinkCursor 0.75s step-end infinite';

            // Dodaj style animacji do dokumentu, jeśli jeszcze nie istnieją
            if (!document.querySelector('#typing-cursor-style')) {
                const style = document.createElement('style');
                style.id = 'typing-cursor-style';
                style.textContent = `
                    @keyframes blinkCursor {
                        from, to { opacity: 0; }
                        50% { opacity: 1; }
                    }
                `;
                document.head.appendChild(style);
            }
        }

        // Wyczyść element i dodaj kontenery
        el.textContent = '';
        el.appendChild(textSpan);
        if (options.cursor) el.appendChild(cursorSpan);

        // Funkcja animacji
        const animate = async () => {
            if (options.startDelay > 0) {
                await new Promise(r => setTimeout(r, options.startDelay));
            }

            const type = async (text: string, start: number = 0, isErasing: boolean = false) => {
                const increment = isErasing ? -1 : 1;
                const speed = isErasing ? options.eraseSpeed : options.speed;
                let currentIndex = start;

                while (isErasing ? currentIndex > 0 : currentIndex < text.length) {
                    textSpan.innerHTML = options.preserveWhitespace
                        ? text.substring(0, currentIndex + increment)
                        : text.substring(0, currentIndex + increment);

                    currentIndex += increment;
                    await new Promise(r => setTimeout(r, speed));
                }

                return currentIndex;
            };

            let shouldContinue = true;
            while (shouldContinue) {
                // Pisanie
                await type(originalText);

                if (options.loop) {
                    // Czekaj przed usuwaniem
                    await new Promise(r => setTimeout(r, options.eraseDelay));
                    // Usuwanie
                    await type(originalText, originalText.length, true);
                    // Czekaj przed następną iteracją
                    await new Promise(r => setTimeout(r, options.startDelay));
                } else {
                    shouldContinue = false;
                }
            }

            // Wywołaj callback po zakończeniu
            options.onComplete?.();
        };

        // Przechowaj referencje do funkcji czyszczącej
        const cleanup = () => {
            el.innerHTML = originalText;
        };

        // Zapisz funkcję czyszczącą w elemencie
        // @ts-ignore
        el._typingCleanup = cleanup;

        // Rozpocznij animację
        animate();
    },

    unmounted(el: HTMLElement & { _typingCleanup?: () => void }) {
        // Wywołaj funkcję czyszczącą przy odmontowaniu
        el._typingCleanup?.();
    }
};

// Dodaj plugin dla Vue
export const TypingPlugin = {
    install(app: any) {
        app.directive('typing', vTyping);
    }
};
