# elastix-sms-beta
Elastix SMS is a module just for sending individual and bulk SMS messages. You can create any number of SMPP trunks, 
create groups of numbers, create SMS campaigns and send individual SMS messages

MÓDULO DE ENVÍO DE SMS PARA ELASTIX
 
Contenido
1.Introducción
2.Funcionalidades principales
3.Instalación
4.Troncales
5.Envío de un mensaje SMS
5.1.Número de mensajes necesarios para enviar un SMS
6.Listas
6.1.Creación de una lista mediante un fichero csv de números
6.2.Creación de una lista a partir del CDR
6.3.Administración de listas ya creadas
7.Campañas de envío masivo
7.1.Preparación del fichero csv para campañas
8.Colas de mensajes
9.Servicio de envío de mensajes
 
MÓDULO DE ENVÍO DE SMS PARA ELASTIX
1. Introducción
El presente documento describe las funcionalidades y el uso del módulo de SMS para Elastix.
Este módulo se distribuye gratuitamente mediante licencia GPL v.2., y por tanto se distribuye “tal
cual” y SIN NINGÚN TIPO DE GARANTÍA. Puede conocer más acerca de esta licencia
consultando la URL http://www.opensource.org/licenses/gpl-license.php.
Actualmente este módulo permite el envío de mensajes SMS a través del protocolo SMPP, por lo
que su uso no está ligado a ningún proveedor concreto; únicamente usted necesita contratar el envío
de mensajes SMS a un proveedor que soporte este protocolo. 
También se permiten el envío de mensajes SMS mediante la ejecución de un shell script, esto es, el
módulo de SMS de invoca un shell script para enviar el mensaje SMS. Esta
posibilidad permite utilizar nuestro módulo para enviar mensajes SMS a través de medios aún no
soportados por éste.
2. Funcionalidades principales
Con el módulo de SMS podrá:
1. Enviar mensajes individualmente, o masivamente
2. Crear listas de distribución para la realización recurrente de campañas SMS sobre los
mismos números de teléfono
3. Crear diversos troncales para el envío de mensajes SMS
4. Crear campañas masivas a través de SMS
 
3. Instalación
Para instalar el módulo de SMS necesita disponer previamente de una PBX
Elastix, versión 1.6.x, o versión 2.0.x.. Las instrucciones que encontrará en este manual son para
Elastix versión 2.0.x..
Primero descargue el módulo de SMS de 

Antes de instalar el módulo de SMS debe instalar el Addon Developer de
Elastix:
Para instalar el Addon haga “clic” sobre el botón “Install”.
Una vez tenga instalado el Addon Developer, vaya a la opción “Load Module” del menú
“Developer”, seleccione el fichero que ha descargado anteriormente y haga “clic” en el botón
“Save”.
El módulo se instalará y aparecerá el mensaje de confirmación de instalación del módulo:
Posteriormente a esto si refresca la pantalla aparecerá el menú SMS del módulo que acaba de
instalar:

4. Configuración
La configuración nos permite entrar una serie de información necesaria para el correcto envío de los
SMS.
Se proporcionará la siguiente información:
• Country Code: es el código de país para números locales. Para Chile, por ejemplo, es el
56.
• Mobile Prefixes: prefijos móviles válidos en nuestro país.
• Minimun Mobile Length: número mínimo de cifras para un número de móvil válido en
nuestro país, sin contar el Country Code. Para Chile 9.
• Maximun Mobile Length: número máximo de cifras para un número de móvil válido en
nuestro país, sin contrar en Country Code. Para Chile 9.
El Country Code, Minimun Mobile Length y Maximun Mobile Length son utilizados para enviar un
mensaje SMS. Así, según los datos del ejemplo:
• Un número con el formato 34XXXXXXXXX es un número móvil válido
• Un número con el formato XXXXXXXXX es un número móvil válido
En cambio los Mobile Prefixes son utilizados para obtener los números de teléfono móviles
presentes en el CDR de nuestra PBX.


5. Troncales
Para poder enviar mensajes SMS deberá crear los diferentes troncales por los que se enviarán los
mensajes SMS. Actualmente sólo puede crear troncales hacia servidores SMPP y troncales Bash
Script que invocan un shell script para enviar el mensaje. Estamos trabajando para ofrecer
conectividad con otro tipo de proveedores, como por ejemplo Gateways Portech.
5.1. Troncales SMPP
Para crear un troncal SMPP debe, contratar el servicio de envío con algún proveedor que le ofrezca
una plataforma SMPP para ello. 
Para crear un troncal SMPP vaya a la opción “Trunks” del menú “SMS”:
La opción “Trunks” muestra primeramente los troncales que ya están creados, los cuales puede
editar haciendo “clic” sobre la opción “show” del troncal:
Para crear el troncal deberá introducir la siguiente información:
• Nombre: nombre que le quiere dar a su troncal, para su administración posterior
• Si desea activar o no el troncal (casilla Active). Si no está activo el troncal estará creado,
pero no se podrá utilizar para enviar SMS.
• Tipo de servicio (Service Type). Seleccionar “SMPP”. Al lado del tipo de servicio dispone
de un desplegable con la configuración de diferentes proveedores del tipo de servicio
(actualmente sólo “Iberoxarxa SMS SMPP Platform”); si selecciona uno de los proveedores
preconfigurados automáticamente se rellenarán los campos “servidor y puerto”.
• Servidor: dirección IP o nombre del servidor del tipo de servicio (proporcionado por su
proveedor).
• Usuario y password : nombre de cuenta de usuario y password para utilizar el servicio
(proporcionado por su proveedor).
• Puerto: número de puerto del tipo de servicio (proporcionado por su proveedor).
• Append Country Code. Si está activo se prefijará el número de destino con el código de país.
• Número llamante (Caller ID): hasta 11 caracteres para cadena alfanumérica y hasta 14
caracteres para cadenas numéricas. Los mensajes se señalizarán con éste número.
• Prioridad: prioridad del troncal, siendo 1 el de mayor prioridad. Si tiene más de 1 troncal
creado y manda un SMS sin indicar el troncal a utilizar primero se intentará enviar por el de
mayor prioridad y si no se consigue se enviará por el siguiente troncal de menor prioridad.

5.2. Troncales Bash Script
Los troncales Bash Script permiten enviar los mensajes SMS gestionados por el módulo de SMS
a través de la invocación de un shell script externo. Esta opción es tremendamente
potente, pues permite utilizar el módulo de SMS con métodos de envío de SMS
no implementados. Únicamente debe crear un troncal de este tipo y desarrollar el script de envío de
SMS.
Para crear un troncal Bash Script vaya a la opción “Trunks” del menú “SMS”:
La opción “Trunks” muestra primeramente los troncales que ya están creados, los cuales puede
editar haciendo “clic” sobre la opción “show” del troncal.
Para crear el troncal deberá introducir la siguiente información:
• Nombre: nombre que le quiere dar a su troncal, para su administración posterior
• Si desea activar o no el troncal (casilla Active). Si no está activo el troncal estará creado,
pero no se podrá utilizar para enviar SMS.
• Tipo de servicio (Service Type). Seleccionar “Bash Script”.
• Script: nombre del script que se deberá de invocar para el envío de cada uno de los SMS. Se
introducirá sólo el nombre del script, sin su path.
• Append Country Code. Si está activo se prefijará el número de destino con el código de país.
• Número llamante (Caller ID): hasta 11 caracteres para cadena alfanumérica y hasta 14
caracteres para cadenas numéricas. Los mensajes se señalizarán con éste número, siempre y
cuando la tecnología de envío de SMS implementada en el script lo permita.
• Prioridad: prioridad del troncal, siendo 1 el de mayor prioridad. Si tiene más de 1 troncal
creado y manda un SMS sin indicar el troncal a utilizar primero se intentará enviar por el de
mayor prioridad y si no se consigue se enviará por el siguiente troncal de menor prioridad.
El script de envío de sms se ha de guardar en /var/www/html/libs/sms/script, y debe tener permisos
de ejecución. En este directorio encontrará un ejemplo de script (fichero sample).

6. Envío de un mensaje SMS
Para enviar un mensaje SMS seleccione la opción “Send” del menú “SMS”.
Para enviar un mensaje debe entrar la siguiente información:
• Número de teléfono: el número de teléfono móvil al que quiere enviar el mensaje. Puede
introducirlo manualmente o puede copiarlo desde el libro de direcciones de Elastix haciendo
“clic” sobre el enlace.
• Texto: texto del mensaje a enviar.
• Encolar el mensaje: si activa la casilla el mensaje se encolará en la PBX y se enviará cuando
la cola de mensajes se ejecute; si no activa la casilla el mensaje se enviará inmediatamente
(más lento).
• Troncal: troncal por el que enviará el mensaje.
Tenga en cuenta lo explicado en 6.1. - Número de mensajes necesarios para enviar un SMS, ya que
en función de los caracteres utilizados en su mensaje (acentos, signos especiales, etc …), su SMS
será enviado con 1 o más mensajes.
El envío del mensaje se realizará según la configuración explicada en 4. - Configuración.
Puede seleccionar o no un troncal. Si no selecciona ningún troncal el mensaje se enviará por el
troncal de mayor prioridad, y si no se puede se pasará al siguiente troncal. En el envío del SMS se
utilizará el número de llamante introducido en el troncal.
Para enviar el mensaje haga “clic” sobre el botón “Send”.
Posteriormente al envío del mensaje se le informará del éxito o no de la operación. Tenga en cuenta
que puede que el mensaje no se envíe de forma instantánea, ya que ello depende del proveedor del
servicio que tenga usted contratado.
Si en lugar de enviar el mensaje directamente ha decidido encolarlo consulte 9. - Colas de mensajes
para conocer el funcionamiento de la cola de envío de mensajes del módulo de SMS.

6.1. Número de mensajes necesarios para enviar un SMS
En la implementación actual del módulo de SMS el mensaje se enviará con
alguno de los siguientes 2 métodos, en función de los caracteres contenidos en el mensaje:
• Codificación SMS, si los caracteres contenidos en el mensaje pertenecen al alfabeto
SMS, según norma GSM 03.38.
• Codificación UNICODE, si el mensaje contiene caracteres fuera de la norma GSM
03.38.
En el caso de codificación SMS el mensaje SMS se envía en unidades de 160 caracteres, de forma
que si el SMS está formado por 170 caracteres el mensaje se enviará utilizando 2 SMS.
En el caso de codificación UNICODE el mensaje SMS se envía en unidades de 70 caracteres, de
forma que si el SMS está formado por 170 caracteres el mensaje se enviará utilizando 3 SMS.
En cualquier caso el módulo de SMS   informa en todo momento del número de
mensajes que se utilizarán para enviar el SMS:
Alfabeto SMS:
Hex Dec Character name Character ISO-8859-1 DEC
0x00 0 COMMERCIAL AT @ 64
0x01 1 POUND SIGN £ 163
0x02 2 DOLLAR SIGN $ 36
0x03 3 YEN SIGN ¥ 165
0x04 4 LATIN SMALL LETTER E WITH GRAVE è 232
0x05 5 LATIN SMALL LETTER E WITH ACUTE é 233
0x06 6 LATIN SMALL LETTER U WITH GRAVE ù 249
0x07 7 LATIN SMALL LETTER I WITH GRAVE ì 236
0x08 8 LATIN SMALL LETTER O WITH GRAVE ò 242
0x09 9 LATIN CAPITAL LETTER C WITH CEDILLA Ç 199
0x0A 10 LINE FEED 10
0x0B 11 LATIN CAPITAL LETTER O WITH STROKE Ø 216
0x0C 12 LATIN SMALL LETTER O WITH STROKE ø 248
0x0D 13 CARRIAGE RETURN 13
0x0E 14 LATIN CAPITAL LETTER A WITH RING ABOVE Å 197
0x0F 15 LATIN SMALL LETTER A WITH RING ABOVE å 229
0x10 16 GREEK CAPITAL LETTER DELTA Δ
0x11 17 LOW LINE _ 95
0x12 18 GREEK CAPITAL LETTER PHI Φ
0x13 19 GREEK CAPITAL LETTER GAMMA Γ
0x14 20 GREEK CAPITAL LETTER LAMBDA Λ
0x15 21 GREEK CAPITAL LETTER OMEGA Ω
0x16 22 GREEK CAPITAL LETTER PI Π
0x17 23 GREEK CAPITAL LETTER PSI Ψ
0x18 24 GREEK CAPITAL LETTER SIGMA Σ
0x19 25 GREEK CAPITAL LETTER THETA Θ
0x1A 26 GREEK CAPITAL LETTER XI Ξ
0x1B 27 ESCAPE TO EXTENSION TABLE
0x1B0A 27 10 FORM FEED 12
0x1B14 27 20 CIRCUMFLEX ACCENT ^ 94
0x1B28 27 40 LEFT CURLY BRACKET { 123
0x1B29 27 41 RIGHT CURLY BRACKET } 125
0x1B2F 27 47 REVERSE SOLIDUS (BACKSLASH) \ 92
0x1B3C 27 60 LEFT SQUARE BRACKET [ 91
0x1B3D 27 61 TILDE ~ 126
0x1B3E 27 62 RIGHT SQUARE BRACKET ] 93
0x1B40 27 64 VERTICAL BAR | 124
0x1B65 27 101 EURO SIGN € 164 (ISO-8859-15)
0x1C 28 LATIN CAPITAL LETTER AE Æ 198
0x1D 29 LATIN SMALL LETTER AE æ 230
0x1E 30 LATIN SMALL LETTER SHARP S (German) ß 223
0x1F 31 LATIN CAPITAL LETTER E WITH ACUTE É 201
0x20 32 SPACE 32
0x21 33 EXCLAMATION MARK ! 33
0x22 34 QUOTATION MARK " 34
0x23 35 NUMBER SIGN # 35
0x24 36 CURRENCY SIGN ¤ 164 (ISO-8859-1)
0x25 37 PERCENT SIGN % 37
0x26 38 AMPERSAND & 38
0x27 39 APOSTROPHE ' 39
0x28 40 LEFT PARENTHESIS ( 40
0x29 41 RIGHT PARENTHESIS ) 41
0x2A 42 ASTERISK * 42
0x2B 43 PLUS SIGN + 43
0x2C 44 COMMA , 44
0x2D 45 HYPHEN-MINUS - 45
0x2E 46 FULL STOP . 46
0x2F 47 SOLIDUS (SLASH) / 47
0x30 48 DIGIT ZERO 0 48
0x31 49 DIGIT ONE 1 49
0x32 50 DIGIT TWO 2 50
0x33 51 DIGIT THREE 3 51
0x34 52 DIGIT FOUR 4 52
0x35 53 DIGIT FIVE 5 53
0x36 54 DIGIT SIX 6 54
0x37 55 DIGIT SEVEN 7 55
0x38 56 DIGIT EIGHT 8 56
0x39 57 DIGIT NINE 9 57
0x3A 58 COLON : 58
0x3B 59 SEMICOLON ; 59
0x3C 60 LESS-THAN SIGN < 60
0x3D 61 EQUALS SIGN = 61
0x3E 62 GREATER-THAN SIGN > 62
0x3F 63 QUESTION MARK ? 63
0x40 64 INVERTED EXCLAMATION MARK ¡ 161
0x41 65 LATIN CAPITAL LETTER A A 65
0x42 66 LATIN CAPITAL LETTER B B 66
0x43 67 LATIN CAPITAL LETTER C C 67
0x44 68 LATIN CAPITAL LETTER D D 68
0x45 69 LATIN CAPITAL LETTER E E 69
0x46 70 LATIN CAPITAL LETTER F F 70
0x47 71 LATIN CAPITAL LETTER G G 71
0x48 72 LATIN CAPITAL LETTER H H 72
0x49 73 LATIN CAPITAL LETTER I I 73
0x4A 74 LATIN CAPITAL LETTER J J 74
0x4B 75 LATIN CAPITAL LETTER K K 75
0x4C 76 LATIN CAPITAL LETTER L L 76
0x4D 77 LATIN CAPITAL LETTER M M 77
0x4E 78 LATIN CAPITAL LETTER N N 78
0x4F 79 LATIN CAPITAL LETTER O O 79
0x50 80 LATIN CAPITAL LETTER P P 80
0x51 81 LATIN CAPITAL LETTER Q Q 81
0x52 82 LATIN CAPITAL LETTER R R 82
0x53 83 LATIN CAPITAL LETTER S S 83
0x54 84 LATIN CAPITAL LETTER T T 84
0x55 85 LATIN CAPITAL LETTER U U 85
0x56 86 LATIN CAPITAL LETTER V V 86
0x57 87 LATIN CAPITAL LETTER W W 87
0x58 88 LATIN CAPITAL LETTER X X 88
0x59 89 LATIN CAPITAL LETTER Y Y 89
0x5A 90 LATIN CAPITAL LETTER Z Z 90
0x5B 91 LATIN CAPITAL LETTER A WITH DIAERESIS Ä 196
0x5C 92 LATIN CAPITAL LETTER O WITH DIAERESIS Ö 214
0x5D 93 LATIN CAPITAL LETTER N WITH TILDE Ñ 209
0x5E 94 LATIN CAPITAL LETTER U WITH DIAERESIS Ü 220
0x5F 95 SECTION SIGN § 167
0x60 96 INVERTED QUESTION MARK ¿ 191
0x61 97 LATIN SMALL LETTER A a 97
0x62 98 LATIN SMALL LETTER B b 98
0x63 99 LATIN SMALL LETTER C c 99
0x64 100 LATIN SMALL LETTER D d 100
0x65 101 LATIN SMALL LETTER E e 101
0x66 102 LATIN SMALL LETTER F f 102
0x67 103 LATIN SMALL LETTER G g 103
0x68 104 LATIN SMALL LETTER H h 104
0x69 105 LATIN SMALL LETTER I i 105
0x6A 106 LATIN SMALL LETTER J j 106
0x6B 107 LATIN SMALL LETTER K k 107
0x6C 108 LATIN SMALL LETTER L l 108
0x6D 109 LATIN SMALL LETTER M m 109
0x6E 110 LATIN SMALL LETTER N n 110
0x6F 111 LATIN SMALL LETTER O o 111
0x70 112 LATIN SMALL LETTER P p 112
0x71 113 LATIN SMALL LETTER Q q 113
0x72 114 LATIN SMALL LETTER R r 114
0x73 115 LATIN SMALL LETTER S s 115
0x74 116 LATIN SMALL LETTER T t 116
0x75 117 LATIN SMALL LETTER U u 117
0x76 118 LATIN SMALL LETTER V v 118
0x77 119 LATIN SMALL LETTER W w 119
0x78 120 LATIN SMALL LETTER X x 120
0x79 121 LATIN SMALL LETTER Y y 121
0x7A 122 LATIN SMALL LETTER Z z 122
0x7B 123 LATIN SMALL LETTER A WITH DIAERESIS ä 228
0x7C 124 LATIN SMALL LETTER O WITH DIAERESIS ö 246
0x7D 125 LATIN SMALL LETTER N WITH TILDE ñ 241
0x7E 126 LATIN SMALL LETTER U WITH DIAERESIS ü 252
0x7F 127 LATIN SMALL LETTER A WITH GRAVE à 224


7. Listas
El módulo SMS   permite gestionar listas de números. Estas listas de números
son almacenadas en base de datos y pueden utilizarse para confeccionar campañas.
Usando las listas usted podrá crear, por ejemplo, una lista con los números de sus empleados y otra
con los números de sus clientes más importantes, y utilizar éstas listas para realizar campañas de
comunicación SMS hacia los números que forman parte de estas listas.
Las listas son creadas a partir de un archivo csv (separado por comas) que contiene los números de
teléfono de la campaña o bien a partir de la información de números móviles almacenados en el
CDR de la PBX.
Para gestionar las listas seleccione la opción “List” del menú “SMS”.
Al entrar en esta opción primeramente se muestran las listas actualmente creadas en el sistema.
Puede ver los números de una lista haciendo “clic” sobre el enlace “Numbers” de la lista, o puede
editarla haciendo “clic” sobre el enlace “Show” de la lista.
7.1. Creación de una lista mediante un fichero csv de números
Para crear una lista a partir de un fichero csv de números debe disponer de un fichero csv con los
números que formarán parte de la lista. Este fichero puede crearlo desde el bloc de notas, o
mediante un programa de hoja de cálculo, como Mricrosoft Excel o Open Office.
Para crear el fichero csv mediante Open Office abra el programa de hoja de cálculo y cree una
columna con la lista de números que formarán parte de la lista, y guarde después la hoja de cálculo
en formato csv (separado por comas “,”), y guárdelo en su disco local.
En el ejemplo la lista estará formada por 24 números.
Una vez tenga el fichero csv seleccione la opción “List” del menú “SMS” y haga click sobre el
botón “Create New List”:
Introduzca el nombre que desea dar a la lista, seleccione el fichero csv con los números que
formarán parte de la lista y haga “clic” sobre el botón “Save”.
La lista ya está creada y lista para ser usada. En el caso que el fichero csv contenga números
duplicados éstos se insertarán una sola vez en la lista.


7.2. Creación de una lista a partir del CDR
Puede obtener una lista a partir de los números móviles de teléfonos contenidos en el CDR de su
PBX. Esto es muy útil, por ejemplo, en empresas de servicios Premium, que desean realizar
campañas de marketing a través de SMS, a los clientes que previamente han llamado a través de
teléfono móvil.
Introduzca el nombre que desea dar a la lista, marque la casilla “Get list from CDR” y haga “clic”
sobre el botón “Save”.
Este método crea una lista de números únicos a partir de los números de teléfonos móviles
contenidos en el CDR, según la configuración entrada en 4. - Configuración.


7.3. Administración de listas ya creadas
Puede ver los números de una lista haciendo “clic” sobre el enlace “Numbers” de la lista, o puede
editarla haciendo “clic” sobre el enlace “Show” de la lista.
Al visualizar los números de la lista:
Puede excluir un número de la lista haciendo “clic” sobre el enlace “exclude” del número. Esto no
borra el número de la lista, únicamente se indica que el número no será utilizado. Esta opción es
útil, por ejemplo, cuando un cliente nos indica que no quiere recibir más avisos por SMS.
Puede incluir un número antes excluido de la lista haciendo “clic” sobre el enlace “Include” del
número.
También puede localizar un número determinado introduciéndolo en el campo “Filter” y pulsando
después “Filter”.
Puede incluir manualmente un número en la lista introduciéndolo en el campo “Add number” y
haciendo “clic” después en el botón “Add”.
Puede también editar una lista haciendo “clic” sobre el enlace “Show” de la lista:
Cuando edita una lista puede seleccionar un nuevo fichero csv, y los números que contenga serán
añadidos a la lista. Si la lista contiene un número ya existente en la lista no será añadido.
De la misma forma, puede incluir en la lista nuevos números provenientes del registro CDR de la
PBX.
También puede borrar la lista.


8. Campañas de envío masivo
Con el módulo SMS   puede enviar SMS de forma masiva. Con esta utilidad
usted podrá realizar campañas de marketing a través de SMS, enviar comunicados de forma masiva
a clientes, empleados, etc …
Seleccionando la pestaña “SMS” y dentro de ésta “Campañas” podremos enviar un mensajes SMS
de forma masiva.
Aunque no haya creado aún ninguna campaña siempre aparecerá la campaña “Cola de Salida”,
utilizada para el encolado de SMS. El sistema no permite borrar esta campaña. Puede conocer más
sobre la cola de salida consultando 9. - Colas de mensajes.
Para crear una nueva campaña haga “clic” sobre el botón “Create new campaign”.
Para crear la campaña debe proporcionar la siguiente información:
• Nombre de la campaña, que debe ser único.
• Si lo desea puede entrar un número de teléfono en el campo “Número llamante”. En este
caso la campaña se enviará señalizando cada uno de los mensajes de la campaña con el valor
de ese número. Si no informa este campo se señalizarán los mensajes según la configuración
del troncal o troncales por los que se realicen la campaña.
• También debe indicar la fecha en que se iniciará la campaña y mientras dure la campaña la
hora inicial y final en que se puede ejecutar. Esto es importante, por ejemplo, para evitar que
se manden SMS a horas no recomendables, pudiendo por ejemplo, ejecutar la campaña sólo
entre las 08:00 y las 20:00.
• Posteriormente debe entrar el mensaje que se enviará en la campaña. Tenga en cuenta lo
explicado en 6.1. - Número de mensajes necesarios para enviar un SMS, ya que en función
de los caracteres utilizados en su mensaje (acentos, signos especiales, etc …), su SMS será
enviado con 1 o más mensajes.
• Existe un cuadro de verificación llamado “Crear campaña en pausa”, cuyo funcionamiento
es muy importante. En el caso que se encuentre marcada (opción por defecto) se creará la
campaña en modo “pausa”, es decir, aunque las condiciones de fecha / hora se cumplan no
se ejecutará hasta que se lo indiquemos. En el caso que la casilla no se encuentre marcada la
campaña se creará en modo “pendiente”, de forma que si se cumplen las condiciones de
fecha / hora se ejecutará inmediatamente. Recomendamos crear las campañas en modo
“pausa”, es decir, con la casilla marcada, ya que de esta forma podremos realizar una
comprobación previa de la campaña y realizar modificaciones en el caso que nos hayamos
equivocado.
• Después proporcionaremos un fichero csv con los números a los que enviar la campaña, o
seleccionaremos una de las listas de números creadas en el sistema. Puede consultar 8.1. -
Preparación del fichero csv para campañas para saber más sobre el formato del fichero csv.
• Finalmente indicaremos el troncal por el que queremos que se envíe la campaña, y en el
caso que no se indique se enviará por alguno de los troncales definidos en el sistema.
Cuando haya entrado toda la información haga “clic” sobre el botón “Save” para guardar la
campaña.
Al guardar la campaña ésta nos aparecerá en la lista de campañas. Observe que en el ejemplo, la
campaña está detenida (ya que la hemos creado en modo pausa). Para lanzar la campaña haga “clic”
sobre el enlace “Resume” de la campaña.
Debe tener en cuenta que para que las campañas funcionen tiene que estar corriendo el proceso de
envío de mensajes en su PBX. Este proceso se encarga de obtener los números de las campañas
activas y los entrega a su proveedor de envío de mensajes SMS. Para conocer más sobre este
proceso, y cómo se activa, consulte 10. - Servicio de envío de mensajes.
Puede filtrar la lista de campañas por estado, seleccionando un valor de la lista desplegable
“Status”.
En cualquier momento puede detener la ejecución de una campaña “clic” sobre el enlace “Stop”.
En cualquier momento puede reanudar la ejecución de una campaña detenida haciendo “clic” sobre
el enlace “Resume”.
También puede ver el estado de envío de los números de la campaña haciendo “clic” sobre el enlace
“Numbers” de la campaña:


8.1. Preparación del fichero csv para campañas
Para crear una campaña debe disponer de un fichero csv con los números de teléfono móvil a los
que se enviará la campaña. Este fichero puede crearlo desde el bloc de notas, o mediante un
programa de hoja de cálculo, como Mricrosoft Excel o Open Office.
Para crear el fichero csv mediante Open Office abra el programa de hoja de cálculo y cree una
columna con los números a los que se debe enviar el texto de la campaña, y otra (si lo desea) con el
mensaje personalizado para cada uno de los números, y guarde después la hoja de cálculo en
formato csv (separado por comas “,”), y guárdelo en su disco local.

MÓDULO DE ENVÍO DE SMS PARA ELASTIX
Si lo desea puede especificar un mensaje personalizado para número de teléfono colocando el
mensaje en cuestión en la segunda columna. Si no informa de un mensaje personalizado se envía en
mensaje definido en la campaña, en cambio si para un determinado número se informa de un
mensaje se envía el mensaje del número.
9. Colas de mensajes
La cola de mensajes es un caso particular de campaña, que se utiliza para encolar peticiones de
envío de SMS desde 6. - Envío de un mensaje SMS.
Debe tener en cuenta que para que las colas funcionen tiene que estar corriendo el proceso de envío
de mensajes en su PBX. Para conocer más sobre este proceso, y cómo se activa, consulte 10. -
Servicio de envío de mensajes.
10. Servicio de envío de mensajes
Una de las partes fundamentales del módulo SMS de IBEROXARXA, es el servicio de envío de
mensajes SMS. Si este servicio no está arrancado no funcionan ni las colas de mensajes, ni las
campañas masivas de sms.
Para ejecutar el servicio escriba lo siguiente desde la consola del sistema operativo de su PBX:
service ixxmassivesms start
Si desea que el servicio de envío de mensajes arranque cada vez que se arranque su PBX, escriba lo
siguiente desde la consola del sistema operativo de su PBX:
chkconfig ixxmassivesms on

