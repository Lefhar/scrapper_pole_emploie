<?php

namespace App;

//recherche /offres/recherche/detail/
class Scrapper
{
    public $url;


    public function getOffre($url)
    {
        $extract = new Extraction();
        $page = new Navigationcurl();
        $page->setCurl($url);
        $offre = $page->getCurl();

        $listeOffre = $this->getListeOffre($offre);
        $motif = '#/offres/recherche/detail/(.*?)"#is';
        $moreoffre = $this->getMore($offre);

        preg_match_all($motif, $listeOffre, $out, PREG_PATTERN_ORDER);

        $tabOffre = $out[1];
        preg_match_all('#id="page_(.*?)"#im', $offre, $hashtweet);
        unset($hashtweet[1][0]);
        echo '<br>';
//echo $moreoffre;
        echo '<br>';
        preg_match_all('#0\?motsCles=+(.*?)+tri=0#is', $moreoffre, $testHash);

        echo '<br>';
        $debut = "https://candidat.pole-emploi.fr/offres/recherche.rechercheoffre:afficherplusderesultats/";
        $fin = $testHash[0][0];
        $tabforeach = array();
        foreach ($hashtweet[1] as $ht) {

            if ($ht) {
                // $urlteste = str_replace('afficherplusderesultats/','afficherplusderesultats/'.$ht,$moreoffre);
                $urlteste = $debut . $ht . "/" . $fin;

                $page->setCurl($urlteste);
                $offre2 = $page->getCurl(true);
                $listeOffre2 = $this->getListeOffre($offre2);
                preg_match_all($motif, $listeOffre2, $out2, PREG_PATTERN_ORDER);

                $tabOffre = array_merge($tabOffre, $out2[1]);
            }
        }


        $tabOffre = array_unique($tabOffre);
        //      var_dump($tabOffre);

        foreach ($tabOffre as $row) {
            $mailExtract = "";
            $obj = new Navigationcurl();
            $obj->setCurl("https://candidat.pole-emploi.fr/offres/recherche/detail/" . $row);
            $offreLien = $obj->getCurl();
            // var_dump($offreLien);
            $titre = $this->getTitle($offreLien);
            $nomEntreprise = $this->getEntreprise($offreLien);
            $siteweb = $this->getSiteWeb($offreLien);

            if ($siteweb) {
                $page->setCurl($siteweb);
                $siteoffre = $page->getCurl();
                $mailExtract = $extract->extstres($siteoffre, 'href="mailto:', '"');


                if (!filter_var($mailExtract, FILTER_VALIDATE_EMAIL)) {
                    $mailExtractSearch = $extract->extstres($siteoffre, 'href="/cdn-cgi/l/email-protection#', '"');
                    $mailExtract = $this->cfDecodeEmail($mailExtractSearch);
                }
                if (!filter_var($mailExtract, FILTER_VALIDATE_EMAIL)) {
                    preg_match_all('`[a-z-_]+(@)+[a-z-_]+(.)[a-z-_]{3}`is', $siteoffre, $out, PREG_PATTERN_ORDER);
                    if (!empty($out[0][0])) {
                        if (filter_var($out[0][0], FILTER_VALIDATE_EMAIL)) {
                            $mailExtract = $out[0][0];
                        }
                    }
                }
                if (!filter_var($mailExtract, FILTER_VALIDATE_EMAIL)) {
                    // echo 'no extract';
                    preg_match_all('#href="(.*?)/contact"#i', $siteoffre, $out, PREG_PATTERN_ORDER);

                    if (!empty($out[1])) {
                        // var_dump($out[1][0]);
                        $page->setCurl($out[1][0] . "/contact");
                        $siteexterne = $page->getCurl();

                        $mailExtract = $extract->extstres($siteexterne, 'href="mailto:', '"');
                        if (!filter_var($mailExtract, FILTER_VALIDATE_EMAIL)) {
                            $mailExtractSearch = $extract->extstres($siteexterne, 'href="/cdn-cgi/l/email-protection#', '"');
                            $mailExtract = $this->cfDecodeEmail($mailExtractSearch);
                        }
                        if (!filter_var($mailExtract, FILTER_VALIDATE_EMAIL)) {
                            preg_match_all('`[a-z-_]+(@)+[a-z-_]+(.)[a-z-_]{3}`is', $siteexterne, $out, PREG_PATTERN_ORDER);
                            if (!empty($out[0][0])) {
                                if (filter_var($out[0][0], FILTER_VALIDATE_EMAIL)) {
                                    $mailExtract = $out[0][0];
                                }
                            }
                        }
                    }
                }
                if (filter_var($mailExtract, FILTER_VALIDATE_EMAIL)) {
                    $tab[] = ['Entreprise' => rtrim(trim($nomEntreprise)), 'titre' => $titre, 'url' => $siteweb, 'mail' => $mailExtract];
                }
            }
        }
        var_dump($tab);
        if (!empty($tab)) {
            return $tab;
        } else {
            return false;
        }

    }


    public function getTitle($data)
    {
        $extract = new Extraction();
        return trim($extract->extstres($data, '<h1 itemprop="title" class="t2 title" id="labelPopinDetails">', '</h1>'));
    }

    public function getEntreprise($data)
    {
        $extract = new Extraction();
        $divmedia = $extract->extstres($data, '<div class="media">', '</a>');
        return $extract->extstres($divmedia, '<h3 class="t4 title">', '</h3>');
    }

    public function getSiteWeb($data)
    {
        $extract = new Extraction();
        $divmedia = $extract->extstres($data, '<div class="media">', '</a>');
        $siteweb = $extract->extstres($divmedia, 'shape="rect" href="', '"');
        return str_replace('http://', 'https://', $siteweb);
    }

    public function getListeOffre($data)
    {
        $extract = new Extraction();
        return $extract->extstres($data, 'data-container-type="zone" id="resultatRechercheZone">', '<p class="results-more text-center">');
    }


    public function getMore($data)
    {
        $extract = new Extraction();
        $divExtract = $extract->extstres($data, 'results-more text-center">', '</a></p>');

        $urlExtract = $extract->extstres($divExtract, 'href="', '"');
        if ($urlExtract) {
            return "https://candidat.pole-emploi.fr" . $urlExtract;
        } else {
            return '';
        }

    }

    public function cfDecodeEmail($encodedString)
    {
        $k = hexdec(substr($encodedString, 0, 2));
        for ($i = 2, $email = ''; $i < strlen($encodedString) - 1; $i += 2) {
            $email .= chr(hexdec(substr($encodedString, $i, 2)) ^ $k);
        }
        return $email;
    }


}