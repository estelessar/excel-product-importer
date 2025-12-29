<?php
/**
 * Excel Parser class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EPI_Excel_Parser {
    
    /**
     * Parse Excel/CSV file
     */
    public function parse($filepath) {
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'csv':
                return $this->parse_csv($filepath);
            case 'xlsx':
            case 'xls':
                return $this->parse_excel($filepath);
            default:
                return new WP_Error('invalid_format', __('Desteklenmeyen dosya formatı.', 'excel-product-importer'));
        }
    }
    
    /**
     * Parse CSV file
     */
    private function parse_csv($filepath) {
        $rows = array();
        $headers = array();
        
        if (($handle = fopen($filepath, 'r')) !== false) {
            // Detect delimiter
            $first_line = fgets($handle);
            rewind($handle);
            
            $delimiter = $this->detect_delimiter($first_line);
            
            $row_num = 0;
            while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                // Skip BOM if present
                if ($row_num === 0 && isset($data[0])) {
                    $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0]);
                }
                
                if ($row_num === 0) {
                    $headers = array_map('trim', $data);
                } else {
                    if (count($data) === count($headers)) {
                        $rows[] = array_combine($headers, $data);
                    } elseif (!empty(array_filter($data))) {
                        // Try to match what we can
                        $row_data = array();
                        foreach ($headers as $i => $header) {
                            $row_data[$header] = isset($data[$i]) ? $data[$i] : '';
                        }
                        $rows[] = $row_data;
                    }
                }
                $row_num++;
            }
            fclose($handle);
        }
        
        if (empty($headers)) {
            return new WP_Error('empty_file', __('Dosya boş veya okunamadı.', 'excel-product-importer'));
        }
        
        return array(
            'headers' => $headers,
            'rows' => $rows,
            'total' => count($rows),
            'preview' => array_slice($rows, 0, 5)
        );
    }
    
    /**
     * Parse Excel file (xlsx/xls)
     */
    private function parse_excel($filepath) {
        // Check if PhpSpreadsheet is available
        $autoload = EPI_PLUGIN_DIR . 'vendor/autoload.php';
        
        if (!file_exists($autoload)) {
            // Fallback: Try to use WordPress built-in or simple xlsx parser
            return $this->parse_xlsx_simple($filepath);
        }
        
        require_once $autoload;
        
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filepath);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();
            
            if (empty($data)) {
                return new WP_Error('empty_file', __('Dosya boş.', 'excel-product-importer'));
            }
            
            $headers = array_map('trim', array_shift($data));
            $rows = array();
            
            foreach ($data as $row) {
                if (!empty(array_filter($row))) {
                    $rows[] = array_combine($headers, $row);
                }
            }
            
            return array(
                'headers' => $headers,
                'rows' => $rows,
                'total' => count($rows),
                'preview' => array_slice($rows, 0, 5)
            );
            
        } catch (Exception $e) {
            return new WP_Error('parse_error', $e->getMessage());
        }
    }
    
    /**
     * Simple XLSX parser without PhpSpreadsheet
     */
    private function parse_xlsx_simple($filepath) {
        $zip = new ZipArchive();
        
        if ($zip->open($filepath) !== true) {
            return new WP_Error('zip_error', __('Excel dosyası açılamadı.', 'excel-product-importer'));
        }
        
        // Read shared strings
        $shared_strings = array();
        $shared_strings_xml = $zip->getFromName('xl/sharedStrings.xml');
        
        if ($shared_strings_xml) {
            $xml = simplexml_load_string($shared_strings_xml);
            foreach ($xml->si as $si) {
                if (isset($si->t)) {
                    $shared_strings[] = (string)$si->t;
                } elseif (isset($si->r)) {
                    $text = '';
                    foreach ($si->r as $r) {
                        $text .= (string)$r->t;
                    }
                    $shared_strings[] = $text;
                }
            }
        }
        
        // Read worksheet
        $sheet_xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        
        if (!$sheet_xml) {
            $zip->close();
            return new WP_Error('sheet_error', __('Çalışma sayfası bulunamadı.', 'excel-product-importer'));
        }
        
        $xml = simplexml_load_string($sheet_xml);
        $rows = array();
        
        foreach ($xml->sheetData->row as $row) {
            $row_data = array();
            
            foreach ($row->c as $cell) {
                $value = '';
                $cell_type = (string)$cell['t'];
                
                if ($cell_type === 's') {
                    // Shared string
                    $index = (int)$cell->v;
                    $value = isset($shared_strings[$index]) ? $shared_strings[$index] : '';
                } elseif (isset($cell->v)) {
                    $value = (string)$cell->v;
                }
                
                $row_data[] = $value;
            }
            
            $rows[] = $row_data;
        }
        
        $zip->close();
        
        if (empty($rows)) {
            return new WP_Error('empty_file', __('Dosya boş.', 'excel-product-importer'));
        }
        
        $headers = array_map('trim', array_shift($rows));
        $result_rows = array();
        
        foreach ($rows as $row) {
            if (!empty(array_filter($row))) {
                $row_assoc = array();
                foreach ($headers as $i => $header) {
                    $row_assoc[$header] = isset($row[$i]) ? $row[$i] : '';
                }
                $result_rows[] = $row_assoc;
            }
        }
        
        return array(
            'headers' => $headers,
            'rows' => $result_rows,
            'total' => count($result_rows),
            'preview' => array_slice($result_rows, 0, 5)
        );
    }
    
    /**
     * Detect CSV delimiter
     */
    private function detect_delimiter($line) {
        $delimiters = array(';' => 0, ',' => 0, "\t" => 0, '|' => 0);
        
        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($line, $delimiter));
        }
        
        return array_search(max($delimiters), $delimiters);
    }
}
