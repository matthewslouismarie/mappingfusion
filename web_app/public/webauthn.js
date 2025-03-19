export class WebAuthnCredentialManager
{
  #options;
  #onSuccess;
  #onFailure;

  constructor(options, onSuccess, onFailure) {
    if (!window.PublicKeyCredential) {
      throw Error("Browser does not support passkeys.");
    }

    this.#options = options;
    this.#onSuccess = onSuccess;
    this.#onFailure = onFailure;
  }

  init() {
    console.log('this.#options.challenge', this.#options.challenge);
    return navigator.credentials.create({
      publicKey: {
        // The challenge is produced by the server; see the Security Considerations
        challenge: Uint8Array.from(window.atob(this.#options.challenge), c => c.charCodeAt(0)),

        // Relying Party:
        rp: this.#options.rp,

        // User:
        user: {
          // id: Uint8Array.from(window.atob("MIIBkzCCATigAwIBAjCCAZMwggE4oAMCAQIwggGTMII="), c=>c.charCodeAt(0)), @todo why?
          id: Uint8Array.from(this.#options.user.id, c => c.charCodeAt(0)),
          name: this.#options.user.name,
          displayName: this.#options.user.name,
        },
      
        // This Relying Party will accept either an ES256 or RS256 credential, but
        // prefers an ES256 credential.
        // @todo get info from server
        // @todo add more choice
        pubKeyCredParams: [
          {
            type: "public-key",
            alg: -7 // "ES256" as registered in the IANA COSE Algorithms registry
          },
          // {
          //   type: "public-key",
          //   alg: -257 // Value registered by this specification for "RS256"
          // }
        ],
      
        authenticatorSelection: {
          // Try to use UV if possible. This is also the default. @todo get from server
          userVerification: "preferred"
        },
      
        timeout: 360000,  // 6 minutes @todo get from server
  
        // @todo
        // excludeCredentials: [
        //   // Don’t re-register any authenticator that has one of these credentials
        //   {"id": Uint8Array.from(window.atob("ufJWp8YGlibm1Kd9XQBWN1WAw2jy5In2Xhon9HAqcXE="), c=>c.charCodeAt(0)), "type": "public-key"},
        //   {"id": Uint8Array.from(window.atob("E/e1dhZc++mIsz4f9hb6NifAzJpF1V4mEtRlIPBiWdY="), c=>c.charCodeAt(0)), "type": "public-key"}
        // ],
      
        // // Make excludeCredentials check backwards compatible with credentials registered with U2F
        // extensions: {"appidExclude": "https://acme.example.com"}
      }
    })
    .then(this.#onSuccess)
    .catch(this.#onFailure);
  }

  /**
   * 
   * @param PublicKeyCredential publicKeyCredential 
   */
  processPublicKeyCredential(publicKeyCredential) {
    console.log(publicKeyCredential);
    // 7.1.3
    if (!(publicKeyCredential.response instanceof AuthenticatorAttestationResponse)) {
      throw TypeError('"response" property not of the expected type.');
    }

    // 7.1.4
    const clientExtensionResults = publicKeyCredential.getClientExtensionResults();

    // 7.1.5
    // @todo Is this the good algorithm? Yes as any UTF-8 decoder that does not include the BOM will do.
    const decoder = new TextDecoder('utf-8');
    const JSONtext = decoder.decode(publicKeyCredential.response.clientDataJSON);

    // 7.1.6
    const C = JSON.parse(JSONtext);

    // 7.1.7
    if (C.type !== 'webauthn.create') {
      throw TypeError('"Client data type must be equal to "webauthn.create".');
    }

    console.log(C);

    var decode = function(input) {
        // Replace non-url compatible chars with base64 standard chars
        input = input
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        // Pad out with standard base64 required padding characters
        var pad = input.length % 4;
        if(pad) {
          if(pad === 1) {
            throw new Error('InvalidLengthError: Input base64url string is the wrong length to determine padding');
          }
          input += new Array(5-pad).join('=');
        }

        return input;
    }



    // 7.1.8
    if (decode(C.challenge) !== new TextEncoder().encode(window.atob(this.#options.challenge))) {
      throw TypeError('"Client data challenge differs from the RP’s challenge.');
    }

    // 7.1.9
    if (C.origin !== this.#options.rp.origin) {
      throw TypeError('"Client data type must be equal to "webauthn.create".');
    }

    // 7.1.10
    // @todo Not implemented yet.
    if (C.hasOwnProperty['tokenBinding']) {
      alert('Token binding not implemented yet.');
    }

    // 7.1.11
    const hash = window.crypto.subtle.digest('SHA-256', publicKeyCredential.response.clientDataJSON);

    this.#onSuccess();
  }
}