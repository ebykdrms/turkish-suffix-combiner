<?php

class TurkishSuffixCombiner
{
    private $k,$kArr,$kArrCount,$kSonSesli;
    private $ek,$ekArr,$ekIndex;
    private $ozelAd;
    private $tumEklerBuyuk = false;

    private $birlesim;

    private $sessiz = array(
        'b','c','ç','d','f','g','ğ','h','j','k','l','m','n','p','r','s','ş','t','v','y','z','w','x','q',
        'B','C','Ç','D','F','G','Ğ','H','J','K','L','M','N','P','R','S','Ş','T','V','Y','Z','W','X','Q'
    );
    private $sesli = array('a','ı','o','u','e','i','ö','ü','A','I','O','U','E','İ','Ö','Ü');
    private $kalinSesli = array('a','ı','o','u','A','I','O','U');
    private $inceSesli = array('e','i','ö','ü','E','İ','Ö','Ü');
    private $yuvarlakSesli = array('o','ö','u','ü','O','Ö','U','Ü');
    private $kalinYuvarlakSesli = array('o','u','O','U');
    private $inceYuvarlakSesli = array('ö','ü','Ö','Ü');
    private $duzSesli = array('a','e','ı','i','A','E','I','İ');    
    private $kalinDuzSesli = array('a','ı','A','I');
    private $inceDuzSesli = array('e','i','E','İ');

    public function __construct($kelime='',$ozelAd=false)
    {
        $this->kelime($kelime,$ozelAd);
    }

    public function get() { return $this->birlesim; }    

    public function kelime($kelime,$ozelAd=false,$tumEklerBuyuk=false)
    {        
        if($tumEklerBuyuk) $this->tumEklerBuyuk = true;
        $this->k = $kelime;
        $this->kArr = preg_split('//u', $kelime, -1, PREG_SPLIT_NO_EMPTY);
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

    public function halEki($ek,$buyuk=false)
    {
        $ek = mb_strtolower($ek,'UTF-8');
        if(in_array($ek,$this->halEki_e)) {
            $this->set_kSonSessiziYumusat();
            $ek = $this->halEki_e[$this->ekIndex];
            if(in_array(end($this->kArr),$this->sesli)) $ek = 'y'.$ek;
        }
        elseif(in_array($ek,$this->halEki_i)) {
            $this->set_kSonSessiziYumusat();
            if($this->is_kSonSesliHarfYuvarlak()) $ek = $this->halEki_i[$this->ekIndex+2];
            else $ek = $this->halEki_i[$this->ekIndex];
            if($this->is_kSonHarfSesli()) $ek = 'y'.$ek;
        }
        elseif(in_array($ek,$this->halEki_de)) {
            if($this->is_kSonHarfSertlestirenSessiz()) $ek = $this->halEki_de[$this->ekIndex+2];
            else $ek = $this->halEki_de[$this->ekIndex];
        }
        elseif(in_array($ek,$this->halEki_den)) {
            if($this->is_kSonHarfSertlestirenSessiz()) $ek = $this->halEki_den[$this->ekIndex+2];
            else $ek = $this->halEki_den[$this->ekIndex];
        }
        if($buyuk || $this->tumEklerBuyuk) $ek = $this->set_buyuk($ek);
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

    public function aitlikEki($ek,$buyuk=false)
    {
        $ek = mb_strtolower($ek,'UTF-8');
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
            $ekIlkHarf = preg_split('//u', $ek, -1, PREG_SPLIT_NO_EMPTY);[0];             
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
            $ekIlkHarf = preg_split('//u', $ek, -1, PREG_SPLIT_NO_EMPTY)[0];             
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
        if($buyuk || $this->tumEklerBuyuk) $ek = $this->set_buyuk($ek);
        $this->set_birlesim($ek);
        return $this;
    }

    // ÇOĞUL EKİ: ler
    private $cogulEki_ler = array('ler','lar');

    public function cogulEki($buyuk=false)
    {
        $ek = 'ler';        
        if(in_array($ek,$this->cogulEki_ler)) $ek = $this->cogulEki_ler[$this->ekIndex];
        if($buyuk || $this->tumEklerBuyuk) $ek = $this->set_buyuk($ek);
        $this->set_birlesim($ek);
        return $this;
    }

    // -ki EKİ
    public function ki($buyuk=false)
    {
        $ek = 'ki';
        if($buyuk || $this->tumEklerBuyuk) $ek = $this->set_buyuk($ek);
        $this->set_birlesim($ek);
        return $this;
    }

    // DAHİ ANLAMINDAKİ -de EKİ
    public function dahi($buyuk=false)
    {        
        $this->k = rtrim($this->k).' ';
        $this->kArr = str_split($this->k);
        $this->kArrCount = count($this->kArr);
        $ek = array('de','da')[$this->ekIndex];
        if($buyuk || $this->tumEklerBuyuk) $ek = $this->set_buyuk($ek);
        $this->set_birlesim($ek);
        return $this;
    }

    // KULLANIM KOLAYLAŞTIRICI FONKSİYONLAR
    public function e_hal($buyuk=false) { $this->halEki('e',$buyuk); return $this; }
    public function i_hal($buyuk=false) { $this->halEki('i',$buyuk); return $this; }
    public function de_hal($buyuk=false) { $this->halEki('de',$buyuk); return $this; }
    public function den_hal($buyuk=false) { $this->halEki('den',$buyuk); return $this; }
    public function ler_cogul($buyuk=false) { $this->cogulEki($buyuk); return $this; }
    public function m_aitlik($buyuk=false) { $this->aitlikEki('m',$buyuk); return $this; }
    public function n_aitlik($buyuk=false) { $this->aitlikEki('n',$buyuk); return $this; }
    public function im_aitlik($buyuk=false) { $this->aitlikEki('im',$buyuk); return $this; }
    public function in_aitlik($buyuk=false) { $this->aitlikEki('in',$buyuk); return $this; }
    public function miz_aitlik($buyuk=false) { $this->aitlikEki('miz',$buyuk); return $this; }
    public function niz_aitlik($buyuk=false) { $this->aitlikEki('niz',$buyuk); return $this; }
    public function imiz_aitlik($buyuk=false) { $this->aitlikEki('miz',$buyuk); return $this; }
    public function iniz_aitlik($buyuk=false) { $this->aitlikEki('niz',$buyuk); return $this; }
    public function leri_aitlik($buyuk=false) { $this->aitlikEki('leri',$buyuk); return $this; }


    private function set_birlesim($ek='')
    {
        if($this->ozelAd===true) $this->birlesim = $this->k."'".$ek;
        else $this->birlesim = $this->k.$ek;

        $this->ozelAd = false;
        $this->kelime($this->birlesim,false);
    }

    private function set_buyuk($str)
    {
        $str = str_replace('i','İ',$str);
        $str = str_replace('ö','Ö',$str);
        $str = str_replace('ü','Ü',$str);
        return mb_strtoupper($str,'utf-8');
    }


    // ÜNSÜZ BENZEŞMESİ İŞLEMLERİ
    private $sertlestirenSessiz = array('f','s','t','k','ç','ş','h','p','F','S','T','K','Ç','Ş','H','P');
    private $sertlesecekSessiz = array('c','d','ğ','C','D','Ğ');
    private $sertlesmisSessiz = array('ç','t','k','Ç','T','K');
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
    private function is_kSonSesliHarfYuvarlak() { return in_array($this->kSonSesli,$this->yuvarlakSesli); }
    private function is_kSonSesliHarfDuz() { return in_array($this->kSonSesli,$this->duzSesli); }
    private function is_kSonSesliHarfKalinYuvarlak() { return in_array($this->kSonSesli,$this->kalinYuvarlakSesli); }
    private function is_kSonSesliHarfInceYuvarlak() { return in_array($this->kSonSesli,$this->inceYuvarlakSesli); }
    private function is_kSonSesliHarfKalinDuz() { return in_array($this->kSonSesli,$this->kalinDuzSesli); }
    private function is_kSonSesliHarfInceDuz() { return in_array($this->kSonSesli,$this->inceDuzSesli); }
}
