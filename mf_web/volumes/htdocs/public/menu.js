class Menu
{
    #button;
    #containerHtmlId;
    #menu;

    constructor(buttonId, containerHtmlId, menuId) {
        this.#containerHtmlId = containerHtmlId;
        this.#button = document.getElementById(buttonId);
        this.#menu = document.getElementById(menuId);
    }

    init() {
        document.getElementById(this.#containerHtmlId).classList.add('-js');

        this.#button.onclick = (ev) => {
            if (this.#menu.classList.contains('-open')) {
                this.#menu.classList.remove('-open');
            } else {
                this.#menu.classList.add('-open');
            }
        };
    }
}

document.getElementById('montserrat-font').disabled = false;
const menu = new Menu('menu-button', 'menu-container', 'menu', 'search-form');
menu.init();