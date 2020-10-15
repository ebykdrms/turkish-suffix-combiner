# Neden?

PHP ile bir metin içine dinamik bir kelime eklemeniz gerekiyor. 
Bu kelimenin ne olduğu önceden belli değil ama mutlaka Türkçe ek de alması gerekiyor.
Bu durumda bu sınıfı kullanabilirsiniz.

# Örnekler:

$combiner = new TurkishSuffixCombiner();
echo $combiner->kelime('Emre',true)->aitlikEki('in')->get(); // Emre'nin<br />
echo $combiner->kelime('Ordu',false)->aitlikEki('nız')->get(); // Ordunuz<br />
echo $combiner->kelime('Tuzluk',false)->aitlikEki('nız')->get(); // Tuzluğunuz<br />
echo $combiner->kelime('Su',false)->halEki('a')->get(); // Suya<br />
echo $combiner->kelime('Tuzluk',false)->aitlikEki('m')->halEki('da')->ki()->aitlikEki('in')->dahi()->get(); // Tuzluğumdakinin de<br />
echo $combiner->kelime('Tuzluk',false)->m_aitlik()->de_hal()->ki()->ler_cogul()->den_hal()->dahi()->get(); // Tuzluğumdakilerden de<br />

# Kullanım:

kelime() fonksiyonu kök kelimeyi belirttiğiniz fonksiyondur. Üç parametre alır:
1) Kök kelime
2) Kök kelimenin özel ad olup olmadığı (Özel ad ise bitişik ekler kesme işaretiyle ayrılacaktır)
3) (isteğe bağlı) Kök kelimeye gelecek eklerin büyük harf olup olmaması. true girilirse tüm ekler büyük harf olur.

halEki() fonksiyonu yalnızca şu değerleri alabilir:
> ismin -e hali için....: e, a (koda)<br />
> ismin -i hali için....: i, ı, ü, u (kodu)<br />
> ismin -de hali için...: de, da (koda)<br />
> ismin -den hali için..: den, dan (koddan)<br />

aitlikEki() fonksiyonu yalnızca şu değerleri alabilir:
> benim anlamı için.....: m, im, ım, üm, um (kodum)<br />
> senin anlamı için.....: n, in, ın, un, ün (kodun)<br />
> onun anlamı için......: i, ı, ü, u (kodu)<br />
> bizim anlamı için.....: miz, mız, müz, muz, imiz, ımız, ümüz, umuz (kodumuz)<br />
> sizin anlamı için.....: niz, nız, nüz, nuz, iniz, ınız, ünüz, unuz (kodunuz)<br />
> onların anlamı için...: leri, ları (kodları)<br />

cogulEki() fonksiyonu parametre almaz. Kelimele çoğul anlamı katan -ler ekini ekler.

ki() fonksiyonu parametre almaz. Kelimeye bitişik -ki eki ekler.

dahi() fonksiyonu parametre almaz. Kelimeye dahi anlamındaki -de ekini (ayrı olarak) ekler.

Fonksiyonlar zincir şeklinde kullanılabilir. Sıralama önemlidir: Ekler, zincirdeki sırasına göre eklenir.<br />
Sonuç almak için get() fonksiyonu kullanılır.<br />
$combiner = new TurkishSuffixCombiner();<br />
echo $combiner->kelime('kod')->aitlikEki('m')->halEki('de')->ki()->aitlikEki('in')->dahi()->get(); // kodumdakinin de<br />
echo $combiner->kelime('kod')->m_aitlik()->de_hal()->ki()->in_aitlik()->dahi()->get(); // kodumdakinin de<br />
    
Eklerin büyük harf olarak oluşmasını istiyorsanız iki yol var:
> Büyük harf olmasını istediğiniz ekin fonkisyonuna son parametre olarak true ekleyin.<br />
> Tüm eklerin mutlaka büyük olmasını istiyorsanız kelime() fonksiyonuna 3.parametre olarak true ekleyin.
