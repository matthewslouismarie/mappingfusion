class UnsavedChanges
{
    #buttonId;
    #formId;
    #formSubmitting;
    #isInit;

    constructor(buttonId, formId) {
        this.#buttonId = buttonId;
        this.#formId = formId;
        this.#formSubmitting = false;
        this.#isInit = false;
    }

    init() {
        if (!this.#isInit) {
            document.getElementById(this.#buttonId).onclick = (e) => {
                this.#formSubmitting = true;
            };
            
            window.addEventListener("beforeunload", (e) => {
                if (this.#formSubmitting) {
                    return undefined;
                }
    
                var confirmationMessage = 'It looks like you have been editing something. '
                                        + 'If you leave before saving, your changes will be lost.';
                
                (e || window.event).returnValue = confirmationMessage; //Gecko + IE
                return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
            });

            this.#isInit = true;
        }
    }
}