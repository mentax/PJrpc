# How to MentaxRPC

Przykład działajacy jest w public_html:
- pamiętaj że jeżeli odpalasz na wirtualce w kliencie musisz wpisac adres który widzi wirtualka
- xdebug dziala po stronie klienta, JEŻELI potrzebujesz zdebugowac od strony serwera to masz 2 wyjscia:
	- w kliencie ustawiasz flage ze chcesz debugowac serwer, po wyjsciu z klienta (moment gdy wysyla http request do serwera) debug zawiesi sie na 2s (timeout), klient dostanie odpowiedź ze nic nie zostało zwrócone i generalnie jest "źle", ale Ty bedziesz mógł zdebugować co dostaje serwer, co robi i co zwraca (jeżeli potrzebujesz zdebugowac tylko klienta (to co wysyla, i to co robi z odpowiedzia, nie przelaczaj tej flagi))
	- po stronie "serwera" odpalasz sobie metode recznie i wszystko sprawdzasz (tworzysz obiekt serwera i w kodzie php sobie odpalasz metode ktora Cie interesuje)
	
-  Pamiętaj żeby SERWER jakiś sensowny ERROR_HANDLER bo inaczej bedzie Ci bardzo ciezko znalezc problemy (dodatkow php7.1 rzuca `TypeError` który nie wpada domyślnie do error_handlera)
	
# DataTime (i jej pochodne)
- DataTime działą jako parametr w metodach API
- DataTime **NIE DZIALA** (i powoduje rozwalanie komunikacji) gdy jest właściwościa obiektu który jest parametrem:
	- JsonMapper ma problem z rozpakowaniem DateTime gdyż ten się domyslnie JsonSerializuje do obiektu z 3 wlasciwościami (ktore JsonMapper probuje uruchomic set* ale te nie przyjmuja stringa tylko obiekty)
	- Jezeli jest to konieczne można uzyć `JSONDateTimeImmutable` on ma swój wlasny JsonSerialize które po prostu zwraca string które JsonMapper swietnie ogarnia.
	
# Logi
Jeżeli serwer i klient sa na jednej maszynie najlepiej podac im ten sam folder, wtedy powstaje jeden plik HTML który zawiera wszystko w kolejności w jakiej zostało uruchomione (widac co wyslal klient, co otrzymał serwer, jak to zhydrowal, co odpowiedział, co dostał klient, jak to zhydrował).

