class ImgPreviewer
{
    #btn;
    #container;
    #link;
    #img;

    constructor(containerId) {
        this.#container = document.getElementById(containerId);
        this.#btn = this.#container.querySelector('[data-type=btn]');
        this.#link = undefined;
        this.#img = undefined;
    }

    init(imgs) {
        imgs.forEach((img) => {
            img.onclick = (ev) => this.display(ev);
        });
        this.#container.onclick = (ev) => {
            if (ev.target.id == this.#container.id) {
                this.hide();
            }
        };
        this.#btn.onclick = () => this.hide();

        document.addEventListener('focus', (ev) => {
            if (this.#container.contains(ev.target)) {
                if (ev.target.dataset.type == 'focus-guard') {
                    ev.preventDefault();
                    ev.stopImmediatePropagation();
                    ev.stopPropagation();
                    if (ev.target.dataset.direction == 'end') {
                        this.#btn.focus();
                    } else if (ev.target.dataset.direction == 'start') {
                        this.#link.focus();
                    }
                }
            } else {
                this.hide();
            }
        }, true);

        document.body.addEventListener('keydown', (e) => {
            if (e.key == "Escape") {
                this.hide();
            }
        });
    }

    display(ev) {
        if (ev.target instanceof HTMLImageElement) {
            var src = ev.target.src;
            var alt = ev.target.alt;
        } else if (ev.target instanceof HTMLAnchorElement) {
            ev.preventDefault();
            var src = ev.target.href;
            var alt = 'Image agrandie';
        } else {
            throw new TypeError('Passed element cannot be previewed as an image because it is not one of the expected types.');
        }
        if (this.#link == null) {
            this.#img = new Image();
            this.#img.classList.add('img');
            this.#link = document.createElement('a');
            this.#link.setAttribute('href', src);
            this.#link.classList.add('link');
            this.#link.appendChild(this.#img);
            this.#container.prepend(this.#link);
        }
        this.#link.href = src;
        this.#img.src = src;
        this.#img.alt = alt;
        this.#container.classList.remove('-hidden');
        this.#btn.focus();
    }

    hide() {
        this.#container.classList.add('-hidden');
    }
}

const scriptRef = document.getElementById('img-previewer-init-script');
const previewer = new ImgPreviewer(scriptRef.dataset.containerId);
previewer.init(document.querySelectorAll(scriptRef.dataset.selectors));