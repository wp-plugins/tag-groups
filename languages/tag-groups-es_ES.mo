��    `        �         (     )     B     Z  x   p  L   �  B   6	  v   y	  7  �	  v   (  �   �  �   ^  A     �   ^  a   �  L   \  <   �  C   �  ?   *  =   j  C   �  �   �  g   �  ?        N     T  -   [     �  <   �     �  X   �     B     I     [  1  n     �  -   �  %   �                    0     7     E  *   Y     �     �  '   �     �  <   �          )     >     R  �  X          *  �   -     �     �     �     �     �  �   �  
   y     �     �     �     �     �  
   �     �     �       	     <   &  	   c  	   m  
   w  	   �  8   �     �  H   �  w     �   �     -  �  2  �   �  '   `   .   �   +   �      �      �   3   
!     >!     B!  �  O!     �"     #     #  �   ?#  W   �#  O   "$  �   r$  l  %  �   p&  �   '  �   �'  [   �(  �   Q)  k   	*  n   u*  D   �*  M   )+  B   w+  C   �+  ^   �+  �   ],  x   D-  G   �-  	   .     .  ?   .  #   W.  O   {.     �.  k   �.     C/     L/     Z/  �  x/     1  /   1  .   D1     s1     y1  !   �1     �1     �1     �1  2   �1     2     2  6   -2     d2  ]   �2     �2     3     3     )3  D  /3     t8     �8  �   �8     /9  #   89     \9     b9     �9  �   �9     +:     7:     V:  '   k:     �:     �:     �:     �:     �:     �:  	   
;  L   ;     a;     s;     �;     �;  ?   �;     �;  Z   �;  �   J<  �   �<     �=    �=  �   �?  -   S@  B   �@  ;   �@      A     A  3   'A     [A     aA             A   6           +       J   8          C   4       Z   N      \      X   <          ^   /   V   )   ?   E                   *           K   D   -       R                  [          L   !   '   T   (      9       	       H   %      Y   B                 7               .   @   $         #   1   ;   U   2             `          _          ]   Q   F   I          
   M       W   =   3   0       :           5      ,                 >      S   &   P   "             G          O        <b>Advanced Styling:</b> <b>Groups and Tabs:</b> <b>Tags or Terms:</b> <b>adjust_separator_size=1 or =0</b> Whether to adjust the separator's size to the size of the following tag. Default: 0 <b>amount=x</b> Maximum amount of tags in one cloud (per group). Default: 40 <b>append="something"</b> Append to each tag label. Default: empty <b>collapsible=1 or =0</b> Whether tabs are collapsible (toggle open/close). Default: general settings in the back end <b>groups_post_id=x</b> Display only groups of which at least one assigned tag is also assigned to the post (or page) with the ID x. If set to 0, it will try to retrieve the current post ID. Default: -1 (all groups displayed). Matching groups will be added to the list specified by the parameter <b>include</b>. <b>hide_empty=1 or =0</b> Whether to hide or show also tags that are not assigned to any post. Default: 1 (hide empty) <b>hide_empty_tabs=1 or =0</b> Whether to hide tabs without tags. Default: 0 (Not implemented for PHP function with second parameter set to 'true'. Not effective with <b>groups_post_id</b>.) <b>include="x,y,..."</b> IDs of tag groups (left column in list of groups) that will be considered in the tag cloud. Empty or not used means that all tag groups will be used. Default: empty <b>largest=x</b> Font-size in pt of the largest tags. Default: 22 <b>mouseover=1 or =0</b> Whether tabs can be selected by hovering over with the mouse pointer (without clicking). Default: general settings in the back end <b>order=ASC or =DESC</b> Whether to sort the tags in ascending or descending order. Default: ASC <b>orderby=abc</b> Which field to use for sorting, e.g. count. Default: name <b>prepend="#"</b> Prepend to each tag label. Default: empty <b>separator="•"</b> A separator between the tags. Default: empty <b>separator_size=12</b> The size of the separator. Default: 12 <b>show_tabs=1 or =0</b> Whether to show the tabs. Default: 1 <b>smallest=x</b> Font-size in pt of the smallest tags. Default: 12 <b>tags_post_id=x</b> Display only tags that are assigned to the post (or page) with the ID x. If set to 0, it will try to retrieve the current post ID. Default: -1 (all tags displayed) <b>taxonomy="x,y,..."</b> Restrict the tags only to these taxonomies. Default: empty (= no restriction) A tag group with the id %s and the label '%s' has been deleted. About Action All groups are deleted and assignments reset. All labels were registered. Assign tags to groups and display them in a tabbed tag cloud Basics By default the function <b>tag_groups_cloud</b> returns the html for a tabbed tag cloud. Cancel Change sort order Cheatin&#8217; uh? Choose the taxonomies for which you want to use tag groups. Default is <b>post_tag</b>. Please note that the tag cloud might not work with all taxonomies and that some taxonomies listed here may not be accessible in the admin backend. If you don't understand what is going on here, just leave the default. Christoph Amthor Clicking will leave this page without saving. Collapsible tabs (toggle open/close). Create Create Group Create a new tag group Delete Delete Groups Display filter menu Do you really want to delete the tag group Edit Edit tag groups Edit the label of an existing tag group Edit this item inline Enable shortcode in sidebar widgets (if not visible anyway). Filter by tag group  Further Instructions Go to the settings. Group Here you can choose a theme for the tag cloud. The path to own themes is relative to the <i>uploads</i> folder of your Wordpress installation. Leave empty if you don't use any.</p><p>New themes can be created with the <a href="http://jqueryui.com/themeroller/" target="_blank">jQuery UI ThemeRoller</a>:
			<ol>
			 <li>On the page "Theme Roller" you can customize all features or pick one set from the gallery. Finish with the "download" button.</li>
			 <li>On the next page ("Download Builder") you will need to select the components "Core", "Widget" and "Tabs". Make sure that before download you enter at the bottom as "CSS Scope" <b>.tag-groups-cloud-tabs</b> (including the dot) and as "Theme Folder Name" the name that you wish to enter below (for example "my-theme" or the name used in the theme gallery - avoid spaces and exotic characters).</li>
			 <li>Then you unpack the downloaded zip file and open the css folder. Inside it you will find a folder with the previously chosen "Theme Folder Name" (containing a folder "images" and files named like "jquery-ui-1.10.2.custom.(min.)css").</li>
			 <li>Copy this folder to your <i>wp-content/uploads</i> folder and enter its name below.</li>
			</ol> I know what I am doing. ID If the optional second parameter is set to 'true', the function returns a multidimensional array containing tag groups and tags. Label Label displayed on the frontend List Number of assigned tags OK On this page you can define tag groups. Tags (or terms) can be assigned to these groups on the page where you edit the tags (terms). Parameters Quick&nbsp;Edit Register Labels Register group labels with WPML Save Save Back End Settings Save Group Save Taxonomy Save Theme Options Settings saved. Shortcode Tabs triggered by hovering mouse pointer (without clicking). Tag Cloud Tag Group Tag Groups Tag group The label cannot be empty. Please correct it or go back. Theme Use jQuery.  (Default is on. Other plugins might override this setting.) Use this button to delete all tag groups and assignments. Your tags will not be changed. Check the checkbox to confirm. Use this button to register all existing group labels with WPML for string translation. This is only necessary if labels have existed before you installed WPML. WPML You can add a pull-down menu to the filters above the list of posts. If you filter posts by tag groups, then only items will be shown that have tags (terms) in that particular group. This feature can be turned off so that the menu won't obstruct your screen if you use a high number of groups. May not work with all custom taxonomies. Doesn't work with more than <b>one</b> taxonomy or with <b>category</b> as taxonomy. You can use a shortcode to embed the tag cloud directly in a post, page or widget or you call the function in the PHP code of your theme. Your back end settings have been saved. Your tag cloud theme settings have been saved. Your tag taxonomy settings have been saved. example http://www.christoph-amthor.de http://www.christoph-amthor.de/software/tag-groups/ new not assigned Project-Id-Version: Tag Groups 0.12.1
Report-Msgid-Bugs-To: http://wordpress.org/tag/tag-groups
POT-Creation-Date: 2014-01-23 09:22:11+00:00
PO-Revision-Date: 2014-01-23 10:26+0100
Last-Translator: Burma Center Prague <info@burma-center.org>
Language-Team: LANGUAGE <LL@li.org>
Language: es_ES
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
X-Generator: Poedit 1.6.3
 <b> Styling Avanzado:</b> <b>Grupos y Etiquetas:</b> <b> Etiquetas o Términos: </b> <b>adjust_separator_size=1 ó =0</b> Ya sea para ajustar el tamaño del separador para el tamaño de la etiqueta siguiente. Por defecto: 0 <b>amount= x</b> Cantidad máxima de etiquetas en una nube (por grupo). Por defecto: 40 <b>append="algo"</b> Anexar a cada etiqueta de la etiqueta. Por defecto: vacío <b>collapsible=1 ó =0</b> Si las pestañas son plegables (alternar abrir / cerrar). Por defecto: configuración general en el extremo posterior <b> groups_post_id = x </b> Pantalla únicos grupos de los que al menos una etiqueta asignada también se asigna a la entrada (o de la página) con el ID de x. Si se establece en 0, se tratará de recuperar el ID de mensaje actual. Por defecto: -1 (todos los grupos muestran). Grupos de juego se añadirán a la lista especificada por el parámetro <b>include</b>. <b>hide_empty=1 ó =0</b> Ya sea para ocultar o mostrar también las etiquetas que no están asignadas a ninguna publicación. Por defecto: 1 (ocultar vacío) <b>hide_empty_tabs=1 ó =0</b> si desea ocultar las pestañas y sin etiquetas. Por defecto: 0 (No implementado para la función de PHP con el segundo parámetro establecido en 'verdadero' No es efectivo con <b> groups_post_id</b>) <b>include="x, y, ..." </b> IDs de grupos de etiquetas (columna de la izquierda en la lista de grupos ) que serán considerados en la nube de etiquetas. Vacío o no utilizado significa que se utilizarán todos los grupos de etiquetas. Por defecto: vacío <b>largest=x</b> Tamaño de las letras en pt de las etiquetas más grandes. Por defecto: 22 <b>mouseover= 1 ó =0</b> Si las pestañas se pueden seleccionar colocando el puntero del ratón por encima (sin hacer clic). Por defecto: configuración general en la parte de atrás <b>order=ASC o =DESC</b> Si desea ordenar las etiquetas en orden ascendente o descendente. Por defecto: ASC <b>orderby=abc</b> ¿Qué campo utilizar para la clasificación, por ejemplo, el recuento. Por defecto: nombre <b>prepend="#"</b> Anteponer para cada etiqueta. Por defecto: vacío <b>separador="• "</b> Un separador entre las etiquetas. Por defecto: vacío <b>separator_size=12</b> El tamaño del separador. Por defecto: 12 <b>Show_tabs=1 ó =0</b> Para mostrar las pestañas. Por defecto: 1 <b>smallest=x</b> Tamaño de las letras en pt de las etiquetas más pequeñas. Por defecto: 12 <b>tags_post_id=x</b> Mostrar sólo etiquetas que se asignan a la publicación (o página) con el ID de x. Si se establece en 0, tratará de recuperar el ID de la publicación actual. Por defecto: -1 (todas las etiquetas aparecen) <b>taxonomy="x, y, ..."</b> Restringir las etiquetas sólo a estas taxonomías. Por defecto: vacío (= sin restricción) Ha sido eliminado un grupo de etiquetas con el id %s y la etiqueta '%s' Acerca de Acción Se eliminan todos los grupos y se restablecen las asignaciones. Se registraron todas las etiquetas. Asignar etiquetas a los grupos y mostrar en una nube de etiquetas por pestañas Fundamentos Por defecto la función <b> tag_groups_cloud </b> devuelve el HTML para una nube de etiquetas por pestañas Cancelar Cambiar orden ¿Haciendo trampa &#8217; eh? Elija las taxonomías para la que desea utilizar los grupos de variables. El valor predeterminado es <b> post_tag </b>. Tenga en cuenta que la nube de etiquetas podría no funcionar con todas las taxonomías y que algunas taxonomías enumeradas aquí pueden no ser accesibles en el backend de administración. Si usted no entiende lo que está pasando aquí, sólo deje el valor predeterminado. Christoph Amthor Al hacer clic dejará esta página sin guardar. Etiquetas plegables (alternar abrir / cerrar). Crear Crear grupo Crear un nuevo grupo de etiquetas Eliminar Eliminar Grupos Mostrar Menú de Filtro ¿Realmente quiere eliminar el grupo de etiquetas? Editar Editar grupos de etiquetas Editar la etiqueta de un grupo de etiquetas existentes Editar este artículo en línea Habilitar código corto en los widgets de la barra lateral  (si no es visible de todos modos) Filtrar por grupo de etiquetas Más Instrucciones Ir a configuración. Grupo Aquí usted puede elegir un tema para la nube de etiquetas. El camino a los propios temas es relativo a los <i>archivos</i> de la carpeta de su instalación de Wordpress. Dejar en blanco si no se usa ninguna.</p><p>Los nuevos temas se pueden crear con el <a href="http://jqueryui.com/themeroller/" target="_blank">jQuery UI ThemeRoller</a>:
			<ol>
			 <li>A página "Theme Roller" puede personalizar todas las características o elegir un conjunto de la galería. Terminar con el botón "download".</li>
			 <li>La página siguiente ("Download Builder") tendrá que seleccionar los componentes "Core", "Widget" y  "Tabs". Asegúrese de que antes de la descarga se introduce en la parte inferior como "CSS Scope" <b>.tag-groups-cloud-tabs</b> (incluyendo el punto) y que "Theme Folder Name" o el nombre que desea introducir a continuación (por ejemplo, "my-theme" o el nombre utilizado en la galería de temas - evitar espacios y caracteres exóticos).</li>
			 <li>Luego de desempaquetar el archivo zip descargado y abrir la carpeta css. En su interior se encuentra una carpeta con el previamente elegido "Theme Folder Name" (que contiene una carpeta "images" y archivos con el nombre como  "jquery-ui-1.10.2.custom.(min.)css").</li>
			 <li>Copiar esta carpeta a su <i>wp-content/uploads</i> e introduzca su nombre a continuación.</li>
			</ol> Sé lo que estoy haciendo. ID Si el segundo parámetro opcional tiene el valor 'verdadero', la función devuelve una matriz multidimensional que contiene grupos de etiquetas y etiquetas. Etiqueta Etiqueta que aparece en el frontend Lista Número asignado de etiquetas OK En esta página puede definir grupos de etiquetas. Etiquetas (o términos) pueden ser asignados a estos grupos en la página donde se editan las etiquetas (términos). Parámetros Búsqueda Rápida &nbsp;Editar Regístrar etiquetas Registro de etiquetas de grupo con WPML Guardar Guardar Configuración Back End Guardar grupo Guardar Taxonomía Guardar Opciones de Temas Opciones guardadas. Shortcode Tabs desencadenados por poner encima el puntero del ratón (sin hacer clic). Nube de etiquetas Grupo de Etiquetas Grupos de Etiquetas Etiquetar Grupo La etiqueta no puede estar vacía. Por favor corrija o regrese. Tema Usar jQuery. (Por defecto está activado. Otras extensiones pueden modificar este ajuste.) Use este botón para eliminar todos los grupos de variables y asignaciones. No se cambiarán las etiquetas. Marque la casilla para confirmar. Utilice este botón para registrar todas las etiquetas de grupo existentes con WPML para la traducción de cadenas. Esto sólo es necesario si las etiquetas han existido antes de instalar WPML. WPML Usted puede agregar un menú desplegable para los filtros por encima de la lista de mensajes. Si filtra los mensajes de los grupos de variables, entonces sólo los artículos que tienen etiquetas (términos) en ese grupo en particular serán mostrados. Esta característica se puede desactivar para que el menú no obstruya la pantalla si utiliza un gran número de grupos. Puede no funcionar con todas las taxonomías personalizadas. No funciona con más de <b> una </b> taxonomía o con <b> categoría </b> como la taxonomía. Puede utilizar un código corto para incrustar la nube de etiquetas directamente en un post, página o widget o llamar a la función en el código PHP de su tema. Su configuración de back-end se ha guardado. Su configuración del tema de la nube de etiquetas se ha guardado. Su configuración de taxonomía de etiqueta se ha guardado. Ejemplo http://www.christoph-amthor.de http://www.christoph-amthor.de/software/tag-groups/ Nuevo No asignados 