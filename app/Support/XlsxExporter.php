<?php
namespace App\Support;

/**
 * Pembuat file .xlsx sederhana tanpa library tambahan.
 * Membutuhkan ekstensi PHP "zip" (aktif secara bawaan di XAMPP/Laragon).
 * Teks ditulis sebagai inline string sehingga isi seperti "=SUM(...)" tidak dieksekusi sebagai rumus.
 */
class XlsxExporter
{
    private const LEBAR = [6, 24, 16, 26, 22, 44, 60, 50, 14, 14, 44, 22];

    /** @param array<int,string> $header  @param array<int,array<int,mixed>> $rows  @return string path file sementara */
    public static function buat(array $header, array $rows, string $namaSheet = 'Data', ?array $lebar = null, array $barisTebal = []): string
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new \ZipArchive();
        if ($zip->open($path, \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file xlsx.');
        }
        $zip->addFromString('[Content_Types].xml', self::contentTypes());
        $zip->addFromString('_rels/.rels', self::relsRoot());
        $zip->addFromString('xl/workbook.xml', self::workbook($namaSheet));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::relsWorkbook());
        $zip->addFromString('xl/styles.xml', self::styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', self::sheet($header, $rows, $lebar ?? self::LEBAR, $barisTebal));
        $zip->close();

        return $path;
    }

    private static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>';
    }

    private static function relsRoot(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
    }

    private static function relsWorkbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
    }

    private static function workbook(string $nama): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="' . self::esc($nama) . '" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private static function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }

    private static function sheet(array $header, array $rows, array $lebarKolom, array $barisTebal = []): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';
        $xml .= '<sheetFormatPr defaultRowHeight="15"/><cols>';
        foreach ($lebarKolom as $i => $lebar) {
            $n = $i + 1;
            $xml .= '<col min="' . $n . '" max="' . $n . '" width="' . $lebar . '" customWidth="1"/>';
        }
        $xml .= '</cols><sheetData>';

        $baris = 1;
        $xml .= '<row r="1">';
        foreach (array_values($header) as $c => $teks) {
            $xml .= self::sel($c, $baris, $teks, true);
        }
        $xml .= '</row>';

        foreach (array_values($rows) as $i => $row) {
            $baris++;
            $tebal = in_array($i, $barisTebal, true);   // mis. judul bagian
            $xml .= '<row r="' . $baris . '">';
            foreach (array_values($row) as $c => $nilai) {
                $xml .= self::sel($c, $baris, $nilai, $tebal);
            }
            $xml .= '</row>';
        }

        return $xml . '</sheetData></worksheet>';
    }

    private static function sel(int $kolom, int $baris, mixed $nilai, bool $tebal): string
    {
        $ref = self::huruf($kolom) . $baris;
        $gaya = $tebal ? ' s="1"' : '';

        if (is_int($nilai) || is_float($nilai)) {
            return '<c r="' . $ref . '"' . $gaya . '><v>' . $nilai . '</v></c>';
        }
        if ($nilai === null || $nilai === '') {
            return '<c r="' . $ref . '"' . $gaya . '/>';
        }

        return '<c r="' . $ref . '"' . $gaya . ' t="inlineStr"><is><t xml:space="preserve">' . self::esc((string) $nilai) . '</t></is></c>';
    }

    private static function huruf(int $indeks): string
    {
        $hasil = '';
        $n = $indeks + 1;
        while ($n > 0) {
            $sisa = ($n - 1) % 26;
            $hasil = chr(65 + $sisa) . $hasil;
            $n = intdiv($n - 1, 26);
        }

        return $hasil;
    }

    private static function esc(string $teks): string
    {
        $teks = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $teks) ?? '';

        return htmlspecialchars($teks, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
