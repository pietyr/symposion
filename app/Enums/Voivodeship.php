<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Polish provinces; values double as filter values in public (Polish) URLs.
 */
enum Voivodeship: string
{
    case LowerSilesian = 'dolnoslaskie';
    case KuyavianPomeranian = 'kujawsko-pomorskie';
    case Lublin = 'lubelskie';
    case Lubusz = 'lubuskie';
    case Lodz = 'lodzkie';
    case LesserPoland = 'malopolskie';
    case Masovian = 'mazowieckie';
    case Opole = 'opolskie';
    case Subcarpathian = 'podkarpackie';
    case Podlaskie = 'podlaskie';
    case Pomeranian = 'pomorskie';
    case Silesian = 'slaskie';
    case HolyCross = 'swietokrzyskie';
    case WarmianMasurian = 'warminsko-mazurskie';
    case GreaterPoland = 'wielkopolskie';
    case WestPomeranian = 'zachodniopomorskie';

    public function label(): string
    {
        return match ($this) {
            self::LowerSilesian => __('Lower Silesian'),
            self::KuyavianPomeranian => __('Kuyavian-Pomeranian'),
            self::Lublin => __('Lublin'),
            self::Lubusz => __('Lubusz'),
            self::Lodz => __('Łódź'),
            self::LesserPoland => __('Lesser Poland'),
            self::Masovian => __('Masovian'),
            self::Opole => __('Opole'),
            self::Subcarpathian => __('Subcarpathian'),
            self::Podlaskie => __('Podlaskie'),
            self::Pomeranian => __('Pomeranian'),
            self::Silesian => __('Silesian'),
            self::HolyCross => __('Holy Cross'),
            self::WarmianMasurian => __('Warmian-Masurian'),
            self::GreaterPoland => __('Greater Poland'),
            self::WestPomeranian => __('West Pomeranian'),
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $voivodeship): array => ['value' => $voivodeship->value, 'label' => $voivodeship->label()],
            self::cases(),
        );
    }
}
