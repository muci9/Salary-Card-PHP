Fluturasi de salariu

Sistemul informatic contabil invechit al unui producator de vin stocheaza pontajele angajatilor in format text care urmeaza urmatorul sablon: 

prenume+prenume|nume|cnp|\[cod_activitate;detalii_activitate;oreminute\*rata_orara,\[...]]|procente_contributii


Exemplu de inregistrare pentru un angajat:

ana+maria|stanciu|2900312123321|\[usoare_2;cules_struguri;120h30m\*10.14/h\],\[usoare_1;curatit_butuci;30h\*10.50/h]|cass5.2%somaj0.5%cas15,8%


Se cere sa scrieti un script PHP care sa primeasca la input un string cu formatul exemplificat mai sus si sa scrie la output formatul de fluturas exemplificat mai jos:

Nume            |STANCIU Ana Maria
CNP             |2900312123321

Cod activitate  |Nume activitate    |Ore   |Rata orara|Suma primita
usoare_1        |curatit butuci     |  30,0| 10,50 RON|  315,00 RON
usoare_2        |cules struguri     | 120,5| 10,14 RON| 1221,87 RON
\-------------------------------------------------------------------
TOTAL BRUT                                              1536,87 RON

Contributii
\-------------------------------------------------------------------
CASS                                              5,2%|   79,92 RON
SOMAJ                                             0,5%|    7,68 RON
CAS                                              15,8%|  242,82 RON
\-------------------------------------------------------------------

TOTAL                                                    1206,45 RON          


EXTRA:
	String-urile sunt introduse dintr-un fisier hard-coded numit 'teste'.
