import { WebAuthnCredentialManager } from "./webauthn.js";

const options = JSON.parse(document.getElementById('public-key-credential-creation-options').innerText);

// @todo Validate on client side before sending it to server
const wa = new WebAuthnCredentialManager(
    options,
    function (publicKeyCredential) {
        document.getElementById('public-key-credential').value = JSON.stringify(publicKeyCredential.toJSON());
        document.getElementById('form').submit();
    },
    (failure) => console.log(111, failure),
);

// @todo Call this function only on button click, once user is ready and page is loaded
wa.init();