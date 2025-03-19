<?php

namespace MF\Auth;

use CBOR\CBOREncoder;
use CBOR\Decoder;
use CBOR\StringStream;
use CBOR\Tag\CBOREncodingTag;
use InvalidArgumentException;
use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Session\SessionManager;
use MF\Repository\MemberRepository;
use RuntimeException;
use UnexpectedValueException;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class WebAuthn
{
    const string SESSION_KEY = 'web_authn_challenge';

    // @todo Store configuration here (rp...)
    public function __construct(
        private Configuration $config,
        private MemberRepository $memberRepository,
        private SessionManager $sessionManager,
    )
    {
    }

    public function getUser(AppObject $user): PublicKeyCredentialUserEntity
    {
        return PublicKeyCredentialUserEntity::create(
            $user['id'],
            $user['uuid'],
            $user['id'],
            null // @todo
        );
    }

    /**
     * @todo Create type PublicKeyCredentialCreationOptions
     * @todo Generate in external class
     * @todo Add more options
     * @todo A registration or authentication ceremony begins with the WebAuthn Relying Party creating a PublicKeyCredentialCreationOptions or PublicKeyCredentialRequestOptions object
     */
    public function getPublicKeyCredentialCreationOptions(): array
    {
        $member = $this->memberRepository->find($this->sessionManager->getCurrentMemberUsername());
        
        // @todo Store challenge id in request.
        $this->sessionManager->setCustom(self::SESSION_KEY, random_bytes(16));

        return [
            'challenge' => base64_encode($this->sessionManager->getCustom(self::SESSION_KEY)),
            'rp' => [
                'id' => $this->config->getSetting('webAuthn.rp.id'),
                'name' => $this->config->getSetting('webAuthn.rp.name'),
                'origin' => $this->config->getSetting('webAuthn.rp.origin'),
            ],
            'user' => [
                'id' => $member['uuid'],
                'name' => $member['id'],
                'displayName' => $member['id'],
            ],
        ];
    }

    /**
     * Registers the given WebAuthn credential.
     * 
     * This function is part of the WebAuthn Registration Ceremony.
     * @todo Create class for AuthenticatorAttestationResponse
     */
    public function registerCredential(array $credential): void
    {
        // @todo Throw exception if challenge is not found.
        $challengeBytes = $this->sessionManager->getCustom(self::SESSION_KEY);

        // 7.1.3
        // @todo Check $response is of the type AuthenticatorAttestationResponse
        $response = $credential['response'];

        // 7.1.4
        $clientExtensionResults = $credential['clientExtensionResults'];

        // var_dump($credential);

        // 7.1.5
        // @todo Not officially a UTF8 encoder, is it a problem?
        $jsonText = $this->base64url_decode($response['clientDataJSON']);

        // 7.1.6
        $C = json_decode($jsonText, associative: true, flags: JSON_THROW_ON_ERROR);

        // 7.1.7
        if ('webauthn.create' !== $C['type']) {
            throw new RuntimeException('Client data’s type must be equal to webauthn.create.');
        }

        // 7.1.8
        if ($challengeBytes !== $this->base64url_decode($C['challenge'])) {
            throw new RuntimeException('Challenges do not match.');
        }

        // 7.1.9
        // @todo Re-read https://www.w3.org/TR/webauthn-3/#sctn-validating-origin
        if ($this->config->getSetting('webAuthn.rp.origin') !== $C['origin']) {
            throw new RuntimeException('Origin do not match.');
        }

        // 7.1.10
        // @todo Implement
        // if (key_exists('crossOrigin', $C)) {
        //     throw new RuntimeException('Not implemented yet.');
        // }

        // 7.1.11
        // @todo Implement
        if (key_exists('topOrigin', $C)) {
            throw new RuntimeException('Not implemented yet.');
        }

        // 7.1.12
        $hash = hash('sha256', $response['clientDataJSON']);

        // 7.1.13
        $decoder = Decoder::create();
        $attestationObjectStringStream = StringStream::create($this->base64url_decode($response['attestationObject']));
        $attestationObject = $decoder->decode($attestationObjectStringStream);
        // var_dump($attestationObject);
        // var_dump($attestationObject['authData']->normalize());
        $fmt = $attestationObject['fmt']->normalize();
        // $authData = $decoder->decode(StringStream::create($attestationObject['authData']->normalize()));
        $attStmt = $attestationObject['attStmt']->normalize();
        // var_dump($authData);
        // json_encode($C);
        // json_encode($response);

        // 7.1.14
        // if (hash('sha256', $this->config->getSetting('webAuthn.rp.id')) !== $authData['rpIdHash']) {
        //     throw new RuntimeException('RP ID hashes differ.');
        // }

        // 7.1.15
        // @todo Not implented.

        // 7.1.16
        // @todo Not implented yet.

        // 7.1.17
        // @todo Not implented yet.

        // 7.1.18
        // @todo Not implented yet.

        // 7.1.19
        // @todo Not implented yet.

        // 7.1.20
        // Not implemented yet.

        // 7.1.21
        // @link https://www.iana.org/assignments/webauthn/webauthn.xhtml
        $attstValidator = match ($fmt) {
            'none' => new class { public function validate($attst) { return [] === $attst; }},
            'packed', 'tpm', 'android-key', 'android-safetynet', 'fido-u2f', 'apple' => throw new RuntimeException('Attestation format not yet supported.'),
            default => throw new RuntimeException('Attestation format not yet supported.'),
        };

        // 7.1.22
        // @link https://www.w3.org/TR/webauthn-3/#sctn-defined-attestation-formats
        if (false === $attstValidator->validate($attStmt)) {
            throw new RuntimeException('Attestation format is not valid.');
        }

        // 7.1.23
        // @todo Not implemented yet.

        // 7.1.24
        // @todo Not implemented yet.

        // 7.1.25
        if (strlen($credential['id']) > 1023) {
            throw new RuntimeException('Credential ID should not be longer than 1023 bytes.');
        }

        // 7.1.26
        // Verify the credential ID is not yet registered for this user.
        $credentialRecord = [
            'type' => $credential['type'],
            'id' => $credential['id'],
            
        ];



        
        // json_decode($this->base64url_decode($credential['response']['clientDataJSON'], true), true, flags: JSON_THROW_ON_ERROR);
        // var_dump($jsonText['challenge']);
        // var_dump($t($this->sessionManager->getCustom(self::SESSION_KEY)));
    }

    /**
     * @author gutzmer@usa.net
     * @link https://www.php.net/manual/en/function.base64-encode.php#103849
     */
    public function base64url_encode($data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @author gutzmer@usa.net
     * @link https://www.php.net/manual/en/function.base64-encode.php#103849
     */
    public function base64url_decode($data): string
    {
        $data_b64 = base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT), strict: true);
        if (false === $data_b64) {
            throw new InvalidArgumentException('$data string contains characters outside the base 64 range.');
        }
        return $data_b64;
    }
}