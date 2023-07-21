class Menu
{
    #button;
    #menu;
    #searchForm;

    constructor(buttonId, containerhtmlId, menuId, searchFormId) {
        document.getElementById(containerhtmlId).classList.add('-js');
        this.#button = document.getElementById(buttonId);
        this.#menu = document.getElementById(menuId);
        this.#searchForm = document.getElementById(searchFormId);

    }

    init() {
        this.#button.onclick = (ev) => {
            if (this.#menu.classList.contains('-open')) {
                this.#menu.classList.remove('-open');
            } else {
                this.#menu.classList.add('-open');
            }
        };
        this.#searchForm.onsubmit = (ev) => {
            ev.preventDefault();
            const query = this.#searchForm.querySelector('[data-type="search-field"]');
            this.#searchForm.action = this.#searchForm.dataset.formAction.concat('#gsc.tab=0&gsc.q=', query.value, '&gsc.sort=');
            this.#searchForm.submit();
        };
    }
}