<?php

/**
 * Created by PhpStorm.
 * User: mihai_tutuian
 * Date: 10/20/18
 * Time: 12:23 PM
 */
class Compari
{

    private $curl, $output;

    public function __construct()
    {

        $this->curl = curl_init();
        $this->output = fopen(__DIR__ . '/compari.ro_data.csv', 'w+');
        fputcsv($this->output, array(
            "Shop Title",
            "Firma",
            "Anul Fondarii",
            "Adresa",
            "Website",
            "email",
            "Facebook",
            "Telefon",
            "Despre Noi",
            "Modalitati Livrare",
            "Modalitati Plata",
            "Modalitati comanda",
            "Logo"
        ), ';');
        $this->getRows();


    }

    public function getRows()
    {

        $source = $this->get("https://www.compari.ro/stores/");
        $dom = new DOMDocument();
        @$dom->loadHTML($source);
        $xpath = new DOMXPath($dom);
        $rows = $xpath->query("(//div[@class='shop-box'])");

        foreach ($rows as $row) {

            $shopURL = $xpath->query(".//div[@class='ratings']/a/@href", $row)->item(0)->textContent;

            echo $shopURL . "\n";
            $this->getDetailsForShop($shopURL);
          

        }


    }

    public function getDetailsForShop($url)
    {

        $metodeComanda = array();

        $source = $this->get($url);

        $exportData = array();

        $dom = new DOMDocument();
        @  $dom->loadHTML($source);
        $xpath = new DOMXPath($dom);

        $shopTitle = $xpath->query("//h1[@class='shop-title']/text()")->item(0)->textContent;
        $denumireaFirmei = $xpath->query("//div[contains(text(),'Denumirea firmei')]//following-sibling::div[1]/text()")->item(0)->textContent;
        $anulFondarii = $xpath->query("//div[contains(text(),'Anul fondarii:')]//following-sibling::div[1]/text()")->item(0)->textContent;
        $adresa = $xpath->evaluate("concat(//div[contains(text(),'Adresa:')]//following-sibling::div[1]/span/span[1]/text(),' ',//div[contains(text(),'Adresa:')]//following-sibling::div[1]/span/span[2]/text(),' ',//div[contains(text(),'Adresa:')]//following-sibling::div[1]/span/span[3]/text())");
        $web = $xpath->query("//div[contains(text(),'Web:')]//following-sibling::div[1]/meta/@content")->item(0)->textContent;
        $email = $xpath->query("//div[contains(text(),'Email:')]//following-sibling::div[1]/a/text()")->item(0)->textContent;
        $facebook=$xpath->query("//div[contains(text(),'Facebook:')]//following-sibling::div[1]/a/text()")->item(0)->textContent;
        $tel = $xpath->query("//div[contains(text(),'Tel')]//following-sibling::div[1]/text()")->item(0)->textContent;
        $despreNoi = $xpath->query("//tr/th[contains(text(),'Despre noi')]//..//following-sibling::tr[1]/td/text()")->item(0)->textContent;
        $modalitatiLivrare = $xpath->query("(//td/b[contains(text(),'Modalitati de livrare')]//..//following-sibling::td[1]/text())");


        foreach ($modalitatiLivrare as $livrare) {

            $metodeLivrare[] = $xpath->query(".", $livrare)->item(0)->textContent;


        }
        $metodeLivrare = implode(',', $metodeLivrare);


        $modalitatiPlata = $xpath->query("(//td/b[contains(text(),'Modalitati de plata:')]//..//following-sibling::td[1]/text())");

        foreach ($modalitatiPlata as $plata) {

            $metodePlata[] = $xpath->query(".", $plata)->item(0)->textContent;
        }
        $metodePlata = implode(',', $metodePlata);


        $modalitatiComanda = $xpath->query("(//td/b[contains(text(),'Modalitati de comanda:')]//..//following-sibling::td[1]/text())");

        foreach ($modalitatiComanda as $comanda) {
            $metodeComanda[] = $xpath->query(".", $comanda)->item(0)->textContent;
        }
        $metodeComanda = implode(',', $metodeComanda);


        $logo = $xpath->query("//div[@class='row']/meta[@itemprop='image']/@content")->item(0)->textContent;

        $exportData[] = trim($shopTitle);
        $exportData[] = trim($denumireaFirmei);
        $exportData[] = trim($anulFondarii);
        $exportData[] = trim($adresa);
        $exportData[] = trim($web);
        $exportData[] = trim($email);
        $exportData[]=trim($facebook);
        $exportData[] = trim($tel);
        $exportData[] = trim($despreNoi);
        $exportData[] = trim($metodeLivrare);
        $exportData[] = trim($metodePlata);
        $exportData[] = trim($metodeComanda);


        if (isset($logo)) {
            preg_match('/\.?(\w+)$/', $logo, $extensions);
            file_put_contents(__DIR__ . "/imagini/" . $shopTitle . ".{$extensions[1]}", $this->get($logo));
            $exportData[] = $shopTitle . "." . $extensions[1];
        } else {
            $exportData[] = "";
        }

        fputcsv($this->output, $exportData, ';');

    }

    public function get($url)
    {

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0',

        ));
        curl_setopt($this->curl, CURLOPT_PROXYUSERPWD, "");
        curl_setopt($this->curl, CURLOPT_PROXY, "");

        sleep(1);

        $source = curl_exec($this->curl);
        file_put_contents(__DIR__ . '/source.html', $source);

        return $source;


    }

}

$obj = new Compari();
