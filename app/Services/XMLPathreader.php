<?php
namespace App\Services;
use App\Services\TempImporterClasses\Odcinek;
use App\Services\TempImporterClasses\OpisPunktu;
use App\Services\TempImporterClasses\Punkt;
use App\Services\TempImporterClasses\Szlak;
use Illuminate\Support\Facades\Log;

class XMLPathreader {
    private $inSzlak = false;
    private $inOpis = false;
    private $inOdcinek = -1;
    private $inPunkt = -1;
    private $inOpisPunktu = -1;
    private $pierwszyop = false;
    private $data = '';
    public $szlak;

    public function __construct($file = null) {
        $this->szlak = new Szlak();
        try {
            if ($file !== null) {
                $xml_parser = xml_parser_create();
                xml_set_object($xml_parser, $this);
                xml_set_element_handler($xml_parser, "startElement", "endElement");
                xml_set_character_data_handler($xml_parser, "characters");

                if (!($fp = fopen($file, "r"))) {
                    throw new \Exception("Could not open XML input: $file");
                }

                while ($data = fread($fp, 4096)) {
                    if (!xml_parse($xml_parser, $data, feof($fp))) {
                        $errorCode = xml_get_error_code($xml_parser);
                        $errorString = xml_error_string($errorCode);
                        $currentLine = xml_get_current_line_number($xml_parser);
                        fclose($fp);
                        xml_parser_free($xml_parser);
                        throw new \Exception("XML error: $errorString at line $currentLine (file line: $currentLine)");
                    }
                }
                xml_parser_free($xml_parser);
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

    }

    public function startElement($parser, $name, $attrs) {
        $this->data = '';

        switch ($name) {
            case "SZLAK":
                $this->szlak->nazwa = $attrs['NAZWA'] ?? '';
                $this->szlak->id = $attrs['ID'] ?? '';
                $this->szlak->wersja = $attrs['WERSJA'] ?? '';
                $this->inSzlak = true;
                break;
            case "ODCINEK":
                $o = new Odcinek();
                $o->id = $attrs['ID'] ?? '';
                $o->typ = $attrs['TYP'] ?? '';
                $o->nazwa = $attrs['NAZWA'] ?? '';
                $o->tru = $attrs['TRUDNOSC'] ?? '';
                $o->uci = $attrs['UCIAZLIWOSC'] ?? '';
                $o->mal = $attrs['MALOWNICZOSC'] ?? '';
                $o->czy = $attrs['CZYSTOSC'] ?? '';
                $o->kolejnosc = $this->text2number($attrs['KOLEJNOSC'] ?? '');

                $this->szlak->odcinki[] = $o;
                $this->inOdcinek = count($this->szlak->odcinki) - 1;
                break;
            case "PUNKT":
                $p = new Punkt();
                $p->etykieta = $attrs['ETYKIETA'] ?? '';
                $p->km = $this->text2number($attrs['KM'] ?? '');
                $p->miejscowosc = $attrs['MIEJSCOWOSC'] ?? '';
                $p->id = $attrs['ID'] ?? '';
                $p->ns = $this->text2number($attrs['NS'] ?? '');
                $p->we = $this->text2number($attrs['WE'] ?? '');
                $p->kolejnosc = $this->text2number($attrs['KOLEJNOSC'] ?? '');
                if ($this->inOdcinek !== -1) {
                    $p->idwewo = $this->szlak->odcinki[$this->inOdcinek]->idwew;
                }
                $this->szlak->punkty[] = $p;
                $this->inPunkt = count($this->szlak->punkty) - 1;
                $this->pierwszyop = true;
                break;
            case "OPISPUNKTU":
                $op = new OpisPunktu();
                $op->typpunktu = $attrs['TYP'] ?? '';
                $op->kolejnosc = $this->text2number($attrs['KOLEJNOSC'] ?? '');

                if ($this->pierwszyop) {
                    $this->szlak->punkty[$this->inPunkt]->opisypunktu[0] = $op;
                    $this->pierwszyop = false;
                } else {
                    $this->szlak->punkty[$this->inPunkt]->opisypunktu[] = $op;
                }
                $this->inOpisPunktu = count($this->szlak->punkty[$this->inPunkt]->opisypunktu) - 1;
                break;
            case "OPIS":
                $this->inOpis = true;
                break;
        }
    }

    public function endElement($parser, $name) {
        $content = trim($this->data);

        if (!empty($content)) {
            if ($name === "OPIS" && $this->inOpis) {
                if ($this->inOdcinek !== -1) {
                    $this->szlak->odcinki[$this->inOdcinek]->opis = $content;
                } elseif ($this->inSzlak) {
                    $this->szlak->opis = $content;
                }
                $this->inOpis = false;
            }

            if ($name === "OPISPUNKTU") {
                $p = $this->szlak->punkty[$this->inPunkt];
                $p->opisypunktu[$this->inOpisPunktu]->opis = $content;
            }
        }

        $this->data = '';

        switch ($name) {
            case "OBSZAR":
                $this->inOdcinek = -1;
                break;
            case "SZLAK":
                $this->inSzlak = false;
                break;
        }
    }

    public function characters($parser, $data) {
        $this->data .= $data;
    }

    private function text2number($text) {
        if (empty($text)) {
            return -1.0;
        }
        $x = filter_var($text, FILTER_VALIDATE_FLOAT);
        return $x === false ? -1.0 : $x;
    }
}






