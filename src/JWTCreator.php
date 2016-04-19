<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Jose;

use Assert\Assertion;
use Jose\Factory\JWEFactory;
use Jose\Factory\JWSFactory;
use Jose\Object\JWKInterface;

final class JWTCreator
{
    /**
     * @var \Jose\EncrypterInterface|null
     */
    private $encrypter = null;

    /**
     * @var \Jose\SignerInterface
     */
    private $signer;

    /**
     * JWTCreator constructor.
     *
     * @param \Jose\SignerInterface $signer
     */
    public function __construct(SignerInterface $signer)
    {
        $this->signer = $signer;
    }

    /**
     * @param \Jose\EncrypterInterface $encrypter
     */
    public function enableEncryptionSupport(EncrypterInterface $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    /**
     * @param mixed                     $payload
     * @param array                     $signature_protected_headers
     * @param \Jose\Object\JWKInterface $signature_key
     *
     * @return string
     */
    public function sign($payload, array $signature_protected_headers, JWKInterface $signature_key)
    {
        $jws = JWSFactory::createJWS($payload);

        $jws = $jws->addSignatureInformation($signature_key, $signature_protected_headers);
        $this->signer->sign($jws);

        return $jws->toCompactJSON(0);
    }

    /**
     * @param string                    $payload
     * @param array                     $encryption_protected_headers
     * @param \Jose\Object\JWKInterface $encryption_key
     *
     * @return string
     */
    public function encrypt($payload, array $encryption_protected_headers, JWKInterface $encryption_key)
    {
        Assertion::notNull($this->encrypter, 'The encryption support is not enabled');

        $jwe = JWEFactory::createJWE($payload, $encryption_protected_headers);
        $jwe = $jwe->addRecipientInformation($encryption_key);
        $this->encrypter->encrypt($jwe);

        return $jwe->toCompactJSON(0);
    }

    /**
     * @return string[]
     */
    public function getSignatureAlgorithms()
    {
        return $this->signer->getSupportedSignatureAlgorithms();
    }

    /**
     * @return string[]
     */
    public function getSupportedKeyEncryptionAlgorithms()
    {
        return null === $this->encrypter ? [] : $this->encrypter->getSupportedKeyEncryptionAlgorithms();
    }

    /**
     * @return string[]
     */
    public function getSupportedContentEncryptionAlgorithms()
    {
        return null === $this->encrypter ? [] : $this->encrypter->getSupportedContentEncryptionAlgorithms();
    }

    /**
     * @return string[]
     */
    public function getSupportedCompressionMethods()
    {
        return null === $this->encrypter ? [] : $this->encrypter->getSupportedCompressionMethods();
    }
}