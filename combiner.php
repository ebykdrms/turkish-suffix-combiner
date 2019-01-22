<?php

$combiner = new TurkishSuffixCombiner();
echo $combiner->kelime('Emre',true)->aitlikEki('nız')->get(); echo '<br />';
echo $combiner->kelime('Murat',true)->aitlikEki('nız')->get(); echo '<br />';
echo $combiner->kelime('Ümit',true)->aitlikEki('nız')->get(); echo '<br />';
echo $combiner->kelime('Ekrem',true)->aitlikEki('nız')->get(); echo '<br />';
echo $combiner->kelime('Orhun',true)->aitlikEki('nız')->get(); echo '<br />';
echo $combiner->kelime('Ordu',false)->aitlikEki('nız')->get(); echo '<br />';
echo $combiner->kelime('Sedat',true)->aitlikEki('nız')->get(); echo '<br />';
echo $combiner->kelime('Ümmiye',true)->aitlikEki('nız')->get(); echo '<br />';
echo $combiner->kelime('Büşra',true)->aitlikEki('nız')->get(); echo '<br />';
echo $combiner->kelime('Tuzluk',false)->aitlikEki('nız')->get(); echo '<hr />';

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
    

    

    public function __construct($kelime='',$ozelAd=false)
    {
        $this->kelime($kelime,$ozelAd);
    }

    public function get() { return $this->birlesim; }    

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
    private $halEki_i = array('i','ı','ü','u');
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

    // AİTLİK EKİ: m, n, i, miz, niz, leri
    private $aitlikEki_m = array('m','m','im','ım','üm','um');
    private $aitlikEki_n = array('n','n','in','ın','ün','un');
    private $aitlikEki_i = array('i','ı','ü','u');
    private $aitlikEki_miz = array('miz','mız','müz','muz','imiz','ımız','ümüz','umuz');
    private $aitlikEki_niz = array('niz','nız','nüz','nuz','iniz','ınız','ünüz','unuz');
    private $aitlikEki_leri = array('leri','ları');

    public function aitlikEki($ek)
    {
        $this->set_kSonSessiziYumusat();
        if(in_array($ek,$this->aitlikEki_m)) {            
            if($ek=='m') {
                if($this->is_kSonHarfSessiz()) {
                    if($this->is_kSonSesliHarfYuvarlak()) $ek = $this->aitlikEki_m[$this->ekIndex+4];
                    else $ek = $this->aitlikEki_m[$this->ekIndex+2];
                }
            }
            else {
                if($this->is_kSonHarfSessiz()) {
                    if($this->is_kSonSesliHarfYuvarlak()) $ek = $this->aitlikEki_m[$this->ekIndex+4];
                    else $ek = $this->aitlikEki_m[$this->ekIndex+2];
                }
                else {
                    if($this->is_kSonSesliHarfYuvarlak()) $ek = 'n'.$this->aitlikEki_m[$this->ekIndex+4];
                    else $ek = 'n'.$this->aitlikEki_m[$this->ekIndex+2];
                }
            }
        }
        elseif(in_array($ek,$this->aitlikEki_n)) {
            if($ek=='n') {
                if($this->is_kSonHarfSessiz()) {
                    if($this->is_kSonSesliHarfYuvarlak()) $ek = $this->aitlikEki_n[$this->ekIndex+4];
                    else $ek = $this->aitlikEki_n[$this->ekIndex+2];
                }
            }
            else {
                if($this->is_kSonHarfSessiz()) {
                    if($this->is_kSonSesliHarfYuvarlak()) $ek = $this->aitlikEki_n[$this->ekIndex+4];
                    else $ek = $this->aitlikEki_n[$this->ekIndex+2];
                }
                else {
                    if($this->is_kSonSesliHarfYuvarlak()) $ek = 'n'.$this->aitlikEki_n[$this->ekIndex+4];
                    else $ek = 'n'.$this->aitlikEki_n[$this->ekIndex+2];
                }
            }
        }
        elseif(in_array($ek,$this->aitlikEki_i)) {
            if($this->is_kSonHarfSesli()) {
                if($this->is_kSonSesliHarfYuvarlak()) $ek = 's'.$this->aitlikEki_i[$this->ekIndex+2];
                else $ek = 's'.$this->aitlikEki_i[$this->ekIndex];
            }
            else {
                if($this->is_kSonSesliHarfYuvarlak()) $ek = $this->aitlikEki_i[$this->ekIndex+2];
                else $ek = $this->aitlikEki_i[$this->ekIndex];
            }
        }
        elseif(in_array($ek,$this->aitlikEki_miz)) {
            $ekIlkHarf = str_split($ek)[0];             
            if(in_array($ekIlkHarf,$this->sesli)) {
                if($this->is_kSonHarfSesli()) {
                    if($this->is_kSonSesliHarfYuvarlak()) $ek = $this->aitlikEki_miz[$this->ekIndex+2];
                    else $ek = $this->aitlikEki_miz[$this->ekIndex];
                }
                else {
                    if($this->is_kSonSesliHarfYuvarlak()) $ek = $this->aitlikEki_miz[$this->ekIndex+6];
                    else $ek = $this->aitlikEki_miz[$this->ekIndex+4];
                }
            }
            else {                             
                if($this->is_kSonHarfSesli()) {              
                    if($this->is_kSonSesliHarfYuvarlak()) $ek = $this->aitlikEki_miz[$this->ekIndex+2];
                    else $ek = $this->aitlikEki_miz[$this->ekIndex];
                }
                else {                    
                    if($this->is_kSonSesliHarfYuvarlak()) $ek = $this->aitlikEki_miz[$this->ekIndex+6];
                    else $ek = $this->aitlikEki_miz[$this->ekIndex+4];
                }
            }
        }
        elseif(in_array($ek,$this->aitlikEki_niz)) {
            $ekIlkHarf = str_split($ek)[0];             
            if(in_array($ekIlkHarf,$this->sesli)) {
                if($this->is_kSonHarfSesli()) {
                    if($this->is_kSonSesliHarfYuvarlak()) $ek = $this->aitlikEki_niz[$this->ekIndex+2];
                    else $ek = $this->aitlikEki_niz[$this->ekIndex];
                }
                else {
                    if($this->is_kSonSesliHarfYuvarlak()) $ek = $this->aitlikEki_niz[$this->ekIndex+6];
                    else $ek = $this->aitlikEki_niz[$this->ekIndex+4];
                }
            }
            else {                             
                if($this->is_kSonHarfSesli()) {              
                    if($this->is_kSonSesliHarfYuvarlak()) $ek = $this->aitlikEki_niz[$this->ekIndex+2];
                    else $ek = $this->aitlikEki_niz[$this->ekIndex];
                }
                else {                    
                    if($this->is_kSonSesliHarfYuvarlak()) $ek = $this->aitlikEki_niz[$this->ekIndex+6];
                    else $ek = $this->aitlikEki_niz[$this->ekIndex+4];
                }
            }
        }
        elseif(in_array($ek,$this->aitlikEki_leri)) {
            $ek = $this->aitlikEki_leri[$this->ekIndex];
        }

        $this->set_birlesim($ek);
        return $this;
    }


    private function set_birlesim($ek='')
    {
        if($this->ozelAd===true) $this->birlesim = $this->k."'".$ek;
        else $this->birlesim = $this->k.$ek;

        $this->ozelAd = false;
        $this->kelime($this->birlesim,false);
    }


    // ÜNSÜZ BENZEŞMESİ İŞLEMLERİ
    private $sertlestirenSessiz = array('f','s','t','k','ç','ş','h','p');
    private $sertlesecekSessiz = array('c','d','ğ');
    private $sertlesmisSessiz = array('ç','t','k');
    private function set_kSonSessiziYumusat()
    {
        if($this->ozelAd===false && $this->is_kSonHarfSertlesmisSessiz()) {            
            $sertlesmisSessizIndex = array_search(end($this->kArr),$this->sertlesmisSessiz);
            $this->kArr[$this->kArrCount-1] = $this->sertlesecekSessiz[$sertlesmisSessizIndex];
            $this->k = implode('',$this->kArr);
        }
    }
    private function set_kSonSessiziSertlestir()
    {
        if($this->ozelAd===false && $this->is_kSonHarfSertlesecekSessiz()) {
            $sertlesecekSessizIndex = array_search(end($this->kArr),$this->sertlesecekSessiz);
            $this->kArr[$this->kArrCount-1] = $this->sertlesmisSessiz[$sertlesecekSessizIndex];
            $this->k = implode('',$this->kArr);
        }
    }

    private function is_kSonHarfSessiz() { return in_array(end($this->kArr),$this->sessiz); }
    private function is_kSonHarfSesli() { return in_array(end($this->kArr),$this->sesli); }
    private function is_kSonHarfSertlestirenSessiz() { return in_array(end($this->kArr),$this->sertlestirenSessiz); }
    private function is_kSonHarfSertlesecekSessiz() { return in_array(end($this->kArr),$this->sertlesecekSessiz); }
    private function is_kSonHarfSertlesmisSessiz() { return in_array(end($this->kArr),$this->sertlesmisSessiz); }
    private function is_kSonSesliHarfYuvarlak() { return in_array($this->kSonSesli,array('ö','o','ü','u')); }
    private function is_kSonSesliHarfDuz() { return in_array($this->kSonSesli,array('e','a','i','ı')); }
    private function is_kSonSesliHarfKalinYuvarlak() { return in_array($this->kSonSesli,array('o','u')); }
    private function is_kSonSesliHarfInceYuvarlak() { return in_array($this->kSonSesli,array('ö','ü')); }
    private function is_kSonSesliHarfKalinDuz() { return in_array($this->kSonSesli,array('a','ı')); }
    private function is_kSonSesliHarfInceDuz() { return in_array($this->kSonSesli,array('e','i')); }
}