<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Ip2Geo;

use Geocoder\Model\Address;

/**
 * Custom Address model for the ip2geo provider.
 *
 * Extends the standard Geocoder Address with additional fields returned by the
 * ip2geo API that have no equivalent in the base model (ASN, currency, flags,
 * continent details, registered country, etc.).
 */
final class Ip2GeoAddress extends Address
{
    private ?string $ip = null;

    private ?string $ipType = null;

    private ?bool $isEu = null;

    private ?string $continentName = null;

    private ?string $continentCode = null;

    private ?string $phoneCode = null;

    private ?string $capital = null;

    private ?string $tld = null;

    private ?string $flagEmoji = null;

    private ?string $flagImg = null;

    private ?string $currencyName = null;

    private ?string $currencyCode = null;

    private ?string $currencySymbol = null;

    private ?int $geonameId = null;

    private ?int $continentGeonameId = null;

    private ?int $countryGeonameId = null;

    private ?int $metroCode = null;

    private ?string $flagEmojiUnicode = null;

    private ?int $registeredCountryGeonameId = null;

    private ?int $accuracyRadius = null;

    private ?string $timeNow = null;

    private ?int $asnNumber = null;

    private ?string $asnName = null;

    private ?string $registeredCountryName = null;

    private ?string $registeredCountryCode = null;

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function withIp(?string $ip): self
    {
        $new = clone $this;
        $new->ip = $ip;

        return $new;
    }

    public function getIpType(): ?string
    {
        return $this->ipType;
    }

    public function withIpType(?string $ipType): self
    {
        $new = clone $this;
        $new->ipType = $ipType;

        return $new;
    }

    public function isEu(): ?bool
    {
        return $this->isEu;
    }

    public function withIsEu(?bool $isEu): self
    {
        $new = clone $this;
        $new->isEu = $isEu;

        return $new;
    }

    public function getContinentName(): ?string
    {
        return $this->continentName;
    }

    public function withContinentName(?string $continentName): self
    {
        $new = clone $this;
        $new->continentName = $continentName;

        return $new;
    }

    public function getContinentCode(): ?string
    {
        return $this->continentCode;
    }

    public function withContinentCode(?string $continentCode): self
    {
        $new = clone $this;
        $new->continentCode = $continentCode;

        return $new;
    }

    public function getPhoneCode(): ?string
    {
        return $this->phoneCode;
    }

    public function withPhoneCode(?string $phoneCode): self
    {
        $new = clone $this;
        $new->phoneCode = $phoneCode;

        return $new;
    }

    public function getCapital(): ?string
    {
        return $this->capital;
    }

    public function withCapital(?string $capital): self
    {
        $new = clone $this;
        $new->capital = $capital;

        return $new;
    }

    public function getTld(): ?string
    {
        return $this->tld;
    }

    public function withTld(?string $tld): self
    {
        $new = clone $this;
        $new->tld = $tld;

        return $new;
    }

    public function getFlagEmoji(): ?string
    {
        return $this->flagEmoji;
    }

    public function withFlagEmoji(?string $flagEmoji): self
    {
        $new = clone $this;
        $new->flagEmoji = $flagEmoji;

        return $new;
    }

    public function getFlagImg(): ?string
    {
        return $this->flagImg;
    }

    public function withFlagImg(?string $flagImg): self
    {
        $new = clone $this;
        $new->flagImg = $flagImg;

        return $new;
    }

    public function getCurrencyName(): ?string
    {
        return $this->currencyName;
    }

    public function withCurrencyName(?string $currencyName): self
    {
        $new = clone $this;
        $new->currencyName = $currencyName;

        return $new;
    }

    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    public function withCurrencyCode(?string $currencyCode): self
    {
        $new = clone $this;
        $new->currencyCode = $currencyCode;

        return $new;
    }

    public function getCurrencySymbol(): ?string
    {
        return $this->currencySymbol;
    }

    public function withCurrencySymbol(?string $currencySymbol): self
    {
        $new = clone $this;
        $new->currencySymbol = $currencySymbol;

        return $new;
    }

    public function getGeonameId(): ?int
    {
        return $this->geonameId;
    }

    public function withGeonameId(?int $geonameId): self
    {
        $new = clone $this;
        $new->geonameId = $geonameId;

        return $new;
    }

    public function getContinentGeonameId(): ?int
    {
        return $this->continentGeonameId;
    }

    public function withContinentGeonameId(?int $continentGeonameId): self
    {
        $new = clone $this;
        $new->continentGeonameId = $continentGeonameId;

        return $new;
    }

    public function getCountryGeonameId(): ?int
    {
        return $this->countryGeonameId;
    }

    public function withCountryGeonameId(?int $countryGeonameId): self
    {
        $new = clone $this;
        $new->countryGeonameId = $countryGeonameId;

        return $new;
    }

    public function getMetroCode(): ?int
    {
        return $this->metroCode;
    }

    public function withMetroCode(?int $metroCode): self
    {
        $new = clone $this;
        $new->metroCode = $metroCode;

        return $new;
    }

    public function getFlagEmojiUnicode(): ?string
    {
        return $this->flagEmojiUnicode;
    }

    public function withFlagEmojiUnicode(?string $flagEmojiUnicode): self
    {
        $new = clone $this;
        $new->flagEmojiUnicode = $flagEmojiUnicode;

        return $new;
    }

    public function getRegisteredCountryGeonameId(): ?int
    {
        return $this->registeredCountryGeonameId;
    }

    public function withRegisteredCountryGeonameId(?int $registeredCountryGeonameId): self
    {
        $new = clone $this;
        $new->registeredCountryGeonameId = $registeredCountryGeonameId;

        return $new;
    }

    public function getAccuracyRadius(): ?int
    {
        return $this->accuracyRadius;
    }

    public function withAccuracyRadius(?int $accuracyRadius): self
    {
        $new = clone $this;
        $new->accuracyRadius = $accuracyRadius;

        return $new;
    }

    public function getTimeNow(): ?string
    {
        return $this->timeNow;
    }

    public function withTimeNow(?string $timeNow): self
    {
        $new = clone $this;
        $new->timeNow = $timeNow;

        return $new;
    }

    public function getAsnNumber(): ?int
    {
        return $this->asnNumber;
    }

    public function withAsnNumber(?int $asnNumber): self
    {
        $new = clone $this;
        $new->asnNumber = $asnNumber;

        return $new;
    }

    public function getAsnName(): ?string
    {
        return $this->asnName;
    }

    public function withAsnName(?string $asnName): self
    {
        $new = clone $this;
        $new->asnName = $asnName;

        return $new;
    }

    public function getRegisteredCountryName(): ?string
    {
        return $this->registeredCountryName;
    }

    public function withRegisteredCountryName(?string $registeredCountryName): self
    {
        $new = clone $this;
        $new->registeredCountryName = $registeredCountryName;

        return $new;
    }

    public function getRegisteredCountryCode(): ?string
    {
        return $this->registeredCountryCode;
    }

    public function withRegisteredCountryCode(?string $registeredCountryCode): self
    {
        $new = clone $this;
        $new->registeredCountryCode = $registeredCountryCode;

        return $new;
    }
}
