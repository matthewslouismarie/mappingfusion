class Menu
{
    #button;
    #containerHtmlId;
    #menu;
    #searchForm;

    constructor(buttonId, containerHtmlId, menuId, searchFormId) {
        this.#containerHtmlId = containerHtmlId;
        this.#button = document.getElementById(buttonId);
        this.#menu = document.getElementById(menuId);
        this.#searchForm = document.getElementById(searchFormId);

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