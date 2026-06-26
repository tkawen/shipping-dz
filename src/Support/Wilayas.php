<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz\Support;

/**
 * Single source of truth for Algeria's 69 wilayas (official codes after the
 * 16 Nov 2025 territorial reform that promoted 11 مقاطعات منتدبة → full wilayas).
 *
 * Public Algerian shipping packages are still capped at 58 — this is the
 * differentiator. Codes/order follow the Journal Officiel.
 */
final class Wilayas
{
    /** id => Arabic name */
    public const AR = [
        1 => 'أدرار', 2 => 'الشلف', 3 => 'الأغواط', 4 => 'أم البواقي', 5 => 'باتنة',
        6 => 'بجاية', 7 => 'بسكرة', 8 => 'بشار', 9 => 'البليدة', 10 => 'البويرة',
        11 => 'تمنراست', 12 => 'تبسة', 13 => 'تلمسان', 14 => 'تيارت', 15 => 'تيزي وزو',
        16 => 'الجزائر', 17 => 'الجلفة', 18 => 'جيجل', 19 => 'سطيف', 20 => 'سعيدة',
        21 => 'سكيكدة', 22 => 'سيدي بلعباس', 23 => 'عنابة', 24 => 'قالمة', 25 => 'قسنطينة',
        26 => 'المدية', 27 => 'مستغانم', 28 => 'المسيلة', 29 => 'معسكر', 30 => 'ورقلة',
        31 => 'وهران', 32 => 'البيض', 33 => 'إليزي', 34 => 'برج بوعريريج', 35 => 'بومرداس',
        36 => 'الطارف', 37 => 'تندوف', 38 => 'تيسمسيلت', 39 => 'الوادي', 40 => 'خنشلة',
        41 => 'سوق أهراس', 42 => 'تيبازة', 43 => 'ميلة', 44 => 'عين الدفلى', 45 => 'النعامة',
        46 => 'عين تموشنت', 47 => 'غرداية', 48 => 'غليزان', 49 => 'تيميمون', 50 => 'برج باجي مختار',
        51 => 'أولاد جلال', 52 => 'بني عباس', 53 => 'عين صالح', 54 => 'عين قزام', 55 => 'توقرت',
        56 => 'جانت', 57 => 'المغير', 58 => 'المنيعة', 59 => 'آفلو', 60 => 'بريكة',
        61 => 'القنطرة', 62 => 'بئر العاتر', 63 => 'العريشة', 64 => 'قصر الشلالة', 65 => 'عين وسارة',
        66 => 'مسعد', 67 => 'قصر البخاري', 68 => 'بوسعادة', 69 => 'الأبيض سيدي الشيخ',
    ];

    /** id => Latin/French name (Yalidine/Yalitec key on names; matches their spelling). */
    public const LATIN = [
        1 => 'Adrar', 2 => 'Chlef', 3 => 'Laghouat', 4 => 'Oum El Bouaghi', 5 => 'Batna',
        6 => 'Béjaïa', 7 => 'Biskra', 8 => 'Béchar', 9 => 'Blida', 10 => 'Bouira',
        11 => 'Tamanrasset', 12 => 'Tébessa', 13 => 'Tlemcen', 14 => 'Tiaret', 15 => 'Tizi Ouzou',
        16 => 'Alger', 17 => 'Djelfa', 18 => 'Jijel', 19 => 'Sétif', 20 => 'Saïda',
        21 => 'Skikda', 22 => 'Sidi Bel Abbès', 23 => 'Annaba', 24 => 'Guelma', 25 => 'Constantine',
        26 => 'Médéa', 27 => 'Mostaganem', 28 => "M'Sila", 29 => 'Mascara', 30 => 'Ouargla',
        31 => 'Oran', 32 => 'El Bayadh', 33 => 'Illizi', 34 => 'Bordj Bou Arréridj', 35 => 'Boumerdès',
        36 => 'El Tarf', 37 => 'Tindouf', 38 => 'Tissemsilt', 39 => 'El Oued', 40 => 'Khenchela',
        41 => 'Souk Ahras', 42 => 'Tipaza', 43 => 'Mila', 44 => 'Aïn Defla', 45 => 'Naâma',
        46 => 'Aïn Témouchent', 47 => 'Ghardaïa', 48 => 'Relizane', 49 => 'Timimoun', 50 => 'Bordj Badji Mokhtar',
        51 => 'Ouled Djellal', 52 => 'Béni Abbès', 53 => 'In Salah', 54 => 'In Guezzam', 55 => 'Touggourt',
        56 => 'Djanet', 57 => "El M'Ghair", 58 => 'El Meniaa', 59 => 'Aflou', 60 => 'Barika',
        61 => 'El Kantara', 62 => 'Bir El Ater', 63 => 'El Aricha', 64 => 'Ksar Chellala', 65 => 'Aïn Oussera',
        66 => 'Messaad', 67 => 'Ksar El Boukhari', 68 => 'Bou Saâda', 69 => 'El Abiodh Sidi Cheikh',
    ];

    /** The 11 wilayas added by the 2025 reform — carriers on the old API may not serve these yet. */
    public const POST_2025 = [59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69];

    public static function count(): int
    {
        return count(self::AR);
    }

    public static function exists(int $id): bool
    {
        return isset(self::AR[$id]);
    }

    public static function ar(int $id): ?string
    {
        return self::AR[$id] ?? null;
    }

    public static function latin(int $id): ?string
    {
        return self::LATIN[$id] ?? null;
    }

    /** Reverse lookup: a Latin or Arabic name → wilaya id (case/accent-insensitive on Latin). */
    public static function idFromName(string $name): ?int
    {
        $name = trim($name);
        foreach (self::AR as $id => $ar) {
            if ($ar === $name) {
                return $id;
            }
        }
        $needle = self::fold($name);
        foreach (self::LATIN as $id => $latin) {
            if (self::fold($latin) === $needle) {
                return $id;
            }
        }

        return null;
    }

    /** Was this wilaya created by the Nov 2025 reform (carrier API may lag)? */
    public static function isPost2025(int $id): bool
    {
        return in_array($id, self::POST_2025, true);
    }

    private static function fold(string $s): string
    {
        $s = mb_strtolower(trim($s));

        return strtr($s, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o', 'û' => 'u', 'ü' => 'u', 'ç' => 'c',
        ]);
    }
}
