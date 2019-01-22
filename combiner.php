<?php

$combiner = new TurkishSuffixCombiner();
echo $combiner->kelime('Emre',true)->cogulEki()->halEki('e')->get();
echo '<br />';
echo $combiner->kelime('Murat',true)->cogulEki()->halEki('e')->get();
echo '<br />';
echo $combiner->kelime('Emre',true)->halEki('i')->get();
echo '<br />';
echo $combiner->kelime('Murat',true)->halEki('i')->get();
echo '<br />';
echo $combiner->kelime('Emre',true)->halEki('de')->get();
echo '<br />';
echo $combiner->kelime('Murat',true)->halEki('de')->get();
echo '<br />';
echo $combiner->kelime('Emre',true)->halEki('den')->get();
echo '<br />';
echo $combiner->kelime('Murat',true)->halEki('den')->get();
echo '<hr />';

class TurkishSuffixCombiner
{
    private $k,$kArr,$kArrCount,$kSonSesli;
    private $ek,$ekArr,$ekIndex;
    private $ozelAd;

    private $birlesim;

    private $sesli = array('a','ı','o','u','e','i','ö','ü');
    private $kalinSesli = array('a','ı','o','u');
    private $inceSesli = array('e','i','ö','ü');
    private $yuvarlakSesli = array('o','ö','u','ü');
    private $kalinYuvarlakSesli = array('o','u');
    private $inceYuvarlakSesli = array('ö','ü');
    private $duzSesli = array('a','e','ı','i');    
    private $kalinDuzSesli = array('a','ı');
    private $inceDuzSesli = array('e','i');

    private $sessiz = array('b','c','ç','d','f','g','ğ','h','j','k','l','m','n','p','r','s','ş','t','v','y','z','w','x','q');
    private $sertlestirenSessiz = array('f','s','t','k','ç','ş','h','p');
    private $sertlesecekSessiz = array('c','d','g');
    private $sertlesmisSessiz = array('ç','t','k');

    

    public function __construct($kelime='',$ozelAd=false)
    {
        $this->kelime($kelime,$ozelAd);
    }

    public function get() { return $this->birlesim; }

    private function set_birlesim($ek='')
    {
        if($this->ozelAd===true) $this->birlesim = $this->k."'".$ek;
        else $this->birlesim = $this->k.$ek;

        $this->ozelAd = false;
        $this->kelime($this->birlesim,false);
    }

    public function kelime($kelime,$ozelAd)
    {
        $this->k = $kelime;
        $this->kArr = str_split($kelime);
        $this->kArrCount = count($this->kArr);
        $this->ozelAd = $ozelAd;

        // kelimenin son sesli harfini alıyorum.
        $this->kSonSesli = '';
        for($i=$this->kArrCount-1; $i>=0; $i--) {
            if(in_array($this->kArr[$i],$this->sesli)) { 
                $this->kSonSesli = $this->kArr[$i]; 
                break; 
            }
        }
        // kelimenin son sesli harfinin kalın/ince oluşuna göre sistemdeki index değerini belirliyorum.
        if(in_array($this->kSonSesli, $this->inceSesli)) $this->ekIndex = 0;
        else $this->ekIndex = 1;

        return $this;
    }

    // HAL EKİ: e, i, de, den
    private $halEki_e = array('e','a');
    private $halEki_i = array('i','ı');
    private $halEki_de = array('de','da','te','ta');
    private $halEki_den = array('den','dan','ten','tan');

    public function halEki($ek)
    {        
        if(in_array($ek,$this->halEki_e)) {
            $ek = $this->halEki_e[$this->ekIndex];
            if(in_array(end($this->kArr),$this->sesli)) $ek = 'y'.$ek;
        }
        elseif(in_array($ek,$this->halEki_i)) {
            $ek = $this->halEki_i[$this->ekIndex];
            if(in_array(end($this->kArr),$this->sesli)) $ek = 'y'.$ek;
        }
        elseif(in_array($ek,$this->halEki_de)) {
            if(in_array(end($this->kArr),$this->sertlestirenSessiz)) $ek = $this->halEki_de[$this->ekIndex+2];
            else $ek = $this->halEki_de[$this->ekIndex];
        }
        elseif(in_array($ek,$this->halEki_den)) {
            if(in_array(end($this->kArr),$this->sertlestirenSessiz)) $ek = $this->halEki_den[$this->ekIndex+2];
            else $ek = $this->halEki_den[$this->ekIndex];
        }

        $this->set_birlesim($ek);

        return $this;
    }

    // ÇOĞUL EKİ: ler
    private $cogulEki_ler = array('ler','lar');

    public function cogulEki($ek='ler')
    {
        if(in_array($ek,$this->cogulEki_ler)) $ek = $this->cogulEki_ler[$this->ekIndex];
        $this->set_birlesim($ek);
        return $this;
    }

    

}