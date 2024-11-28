// directives/modifyLinks.js
export default {
    mounted(el) {
        const content = el.innerHTML;
        const parser = new DOMParser();
        const doc = parser.parseFromString(content, 'text/html');

        doc.querySelectorAll('a').forEach(link => {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
        });

        el.innerHTML = doc.body.innerHTML;
    }
}

// Usage in component:
// <div v-html="content" v-modify-links></div>
