<?php

namespace App\Services;

/**
 * Service สำหรับสร้าง Barcode มาตรฐานสากล Code 128 (Subset B)
 * ทำงานแบบ Pure PHP (Zero-Dependency) รองรับการแสดงผลแบบ SVG Inline และ Data URI
 * สามารถสแกนได้ 100% ด้วยเครื่องอ่านบาร์โค้ด USB/Bluetooth ทุกรุ่น
 */
class BarcodeService
{
    /**
     * รูปแบบความกว้างแท่งบาร์โค้ดและช่องว่างของ Code 128 (107 สัญลักษณ์)
     * แต่ละหลักแสดงขนาดโมดูลสลับกันระหว่าง เส้นทึบ (Bar) และ ช่องว่าง (Space)
     */
    private static array $patterns = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213', // 0-9
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132', // 10-19
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211', // 20-29
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313', // 30-39
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331', // 40-49
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111', // 50-59
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214', // 60-69
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111', // 70-79
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141', // 80-89
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141', // 90-99
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112' // 100-106
    ];

    /**
     * สร้าง Barcode รูปแบบ SVG ที่คมชัดทุกความละเอียดและพิมพ์ได้คมที่สุด
     *
     * @param string $text ข้อความหรือรหัสที่ต้องการแปลง (เช่น 'PC-K9M2X4')
     * @param int $height ความสูงของ Barcode (พิกเซล)
     * @param int $barWidth ความกว้างของโมดูลฐาน
     * @param bool $showText แสดงตัวอักษรใต้แท่งบาร์โค้ดหรือไม่
     * @param string $color สีของเส้นบาร์โค้ด
     * @param string $bgColor สีพื้นหลัง
     * @return string รหัส SVG พร้อมใช้งาน
     */
    public static function generateSvg(
        string $text,
        int $height = 55,
        int $barWidth = 2,
        bool $showText = true,
        string $color = '#0f172a',
        string $bgColor = 'transparent'
    ): string {
        $text = strtoupper(trim($text));
        if (empty($text)) {
            return '';
        }

        // Code 128 subset B: ค่า ASCII 32 - 127
        $values = [104]; // Start B (Index 104)
        $checksum = 104;

        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            $code = ord($char) - 32;
            if ($code < 0 || $code > 95) {
                $code = 0; // Fallback ช่องว่าง
            }
            $values[] = $code;
            $checksum += ($i + 1) * $code;
        }

        // คำนวณ Checksum modulo 103 ตามมาตรฐาน ISO/IEC 15417
        $checksum %= 103;
        $values[] = $checksum;
        $values[] = 106; // Stop Code (Index 106)

        // รวมแท่ง Pattern
        $pattern = '';
        foreach ($values as $val) {
            $pattern .= self::$patterns[$val];
        }

        // Quiet Zone (ขอบเว้นระยะ 10 โมดูล ซ้าย-ขวา ตามมาตรฐานสแกนเนอร์)
        $quietZoneModules = 10;
        $totalModules = $quietZoneModules * 2;
        for ($i = 0; $i < strlen($pattern); $i++) {
            $totalModules += (int)$pattern[$i];
        }

        $svgWidth = $totalModules * $barWidth;
        $barHeight = $showText ? ($height - 18) : $height;
        if ($barHeight < 20) {
            $barHeight = 20;
        }
        $svgHeight = $height;

        $svg = [];
        $svg[] = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" width="100%%" height="%d" style="max-width:%dpx;display:block;margin:0 auto;" preserveAspectRatio="xMidYMid meet">',
            $svgWidth, $svgHeight, $svgHeight, $svgWidth
        );

        if ($bgColor !== 'transparent') {
            $svg[] = sprintf('<rect width="%d" height="%d" fill="%s"/>', $svgWidth, $svgHeight, htmlspecialchars($bgColor));
        }

        $x = $quietZoneModules * $barWidth;
        $isBar = true;

        for ($i = 0; $i < strlen($pattern); $i++) {
            $w = (int)$pattern[$i] * $barWidth;
            if ($isBar) {
                $svg[] = sprintf('<rect x="%d" y="2" width="%d" height="%d" fill="%s"/>', $x, $w, $barHeight, htmlspecialchars($color));
            }
            $x += $w;
            $isBar = !$isBar;
        }

        if ($showText) {
            $textY = $barHeight + 14;
            $svg[] = sprintf(
                '<text x="%d" y="%d" text-anchor="middle" font-family="SFMono-Regular, Consolas, Monaco, monospace" font-size="13" font-weight="700" fill="%s" letter-spacing="2">%s</text>',
                (int)($svgWidth / 2),
                $textY,
                htmlspecialchars($color),
                htmlspecialchars($text)
            );
        }

        $svg[] = '</svg>';

        return implode("\n", $svg);
    }

    /**
     * สร้าง Barcode ในรูปแบบ Data URI (Base64 SVG) สำหรับใส่ใน <img src="...">
     */
    public static function generateDataUri(string $text, int $height = 55, int $barWidth = 2, bool $showText = true): string
    {
        $svg = self::generateSvg($text, $height, $barWidth, $showText, '#0f172a', '#ffffff');
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
