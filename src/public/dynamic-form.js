class DynamicForm
{
    init() {
        document.querySelectorAll('[data-type=add-dynamic-form]').forEach((addBtn) => {
            addBtn.onclick = (ev) => {
                const tplClone = document.getElementById(addBtn.dataset.templateId).content.cloneNode(true);
                this.formatAttributes(tplClone, '{{ i }}', addBtn.dataset.index);
                tplClone.querySelector('[data-type=remove-dynamic-form-button]').onclick = this.onDelClick;
                tplClone.querySelector('[data-type=id]').textContent = addBtn.dataset.index;
                document.getElementById(addBtn.dataset.target).append(tplClone);
                addBtn.dataset.index = parseInt(addBtn.dataset.index) + 1;
            };
        });
        document.querySelectorAll('[data-type=remove-dynamic-form-button]').forEach((value) => {
            value.onclick = this.onDelClick;
        });
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

    onDelClick(ev) {
        let currentElement = ev.target;
        while (currentElement.dataset.type !== 'dynamic-form') {
            currentElement = currentElement.parentNode;
        }
        currentElement.remove();
    }
}

const contribForm = new DynamicForm();
contribForm.init();