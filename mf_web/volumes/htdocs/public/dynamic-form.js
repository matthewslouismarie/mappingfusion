class DynamicForm
{
    #button;
    #sourceTpl;
    #index;

    constructor(buttonId, index = 0) {
        this.#button = document.getElementById(buttonId);
        this.#sourceTpl = document.getElementById(this.#button.dataset.source);
        this.#index = 0;
    }

    init() {
        this.#button.onclick = (ev) => {
            const clone = this.#sourceTpl.content.cloneNode(true);
            this.formatAttributes(clone, '{{ i }}', this.#index);
            clone.querySelector('[data-type=remove-dynamic-form-button]').onclick = (ev) => {
                let currentElement = ev.target;
                while (currentElement.dataset.type !== 'dynamic-form') {
                    currentElement = currentElement.parentNode;
                }
                currentElement.remove();
            };
            this.#button.parentNode.insertBefore(clone, this.#button);
            this.#index += 1;
        };
    }

    formatAttributes(element, pattern, replacement) {
        for (const e of element.children) {
            this.formatAttributes(e, pattern, replacement);
        }
        if (undefined !== element.attributes) {
            for (var i = 0; i < element.attributes.length; i++) {
                var attrib = element.attributes[i];
                if (attrib.specified) {
                    attrib.value = attrib.value.replace(pattern, replacement);
                }
            }
        }
    }
}