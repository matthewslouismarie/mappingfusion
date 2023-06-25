class Menu
{
    #button;
    #menu;

    constructor(buttonId, containerhtmlId, menuId) {
        document.getElementById(containerhtmlId).classList.add('-js');
        this.#button = document.getElementById(buttonId);
        this.#menu = document.getElementById(menuId);
    }

    init() {
        this.#button.onclick = (ev) => {
            if (this.#menu.classList.contains('-open')) {
                this.#menu.classList.remove('-open');
            } else {
                this.#menu.classList.add('-open');
            }
        };
    }
}