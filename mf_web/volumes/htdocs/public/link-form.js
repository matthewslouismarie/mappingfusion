class LinkFormButton
{
    #button;
    #template;
    #targetLocation;

    constructor(buttonId) {
        this.#button = document.getElementById(buttonId);
        this.#targetLocation = document.getElementById(this.#button.dataset.locationTarget);
        this.#template = document.getElementById(this.#button.dataset.template);

        this.#button.onclick = (ev) => {
            const newIndex = document.querySelectorAll('[data-link-form-id=link-form]').length;
            const clone = this.#template.content.cloneNode(true);
            clone.querySelector('[data-type=delete-link-button]').onclick = (ev) => {
                this.deleteLink(ev);
            };
            this.formatAttributes(clone, '${index}', newIndex);
            this.#targetLocation.parentNode.insertBefore(clone, this.#targetLocation);
        };

        document.querySelectorAll('[data-type=delete-link-button]').forEach((value, _i, _p) => {
            value.onclick = (ev) => {
                this.deleteLink(ev);
            };
        });
    }

    deleteLink(ev) {
        console.log(this);
        const btn = ev.target;
        const linkForm = document.getElementById(btn.dataset.formId);
        if (undefined !== btn.dataset.linkId) {
            const template = document.getElementById(btn.dataset.templateId);
            const clone = template.content.cloneNode(true);
            this.formatAttributes(clone, '${linkId}', btn.dataset.linkId);
            linkForm.parentNode.appendChild(clone);   
        }
        linkForm.remove();
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