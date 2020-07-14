<?php

/**
 * Plugin Name: Formulario Autoevaluacion
 * Plugin URI: https://autoevaluacion.com
 * Description: Formulario autoevaluación
 * Version: 1.0
 * Author: Tomás
 * Author URI: www.tomasfermoso.com
 * License: GPLv2 or later
 * Text Domain: formulario
 */

function getRealIP()
{

    if (isset($_SERVER["HTTP_CLIENT_IP"])) {
        return $_SERVER["HTTP_CLIENT_IP"];
    } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        return $_SERVER["HTTP_X_FORWARDED_FOR"];
    } elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
        return $_SERVER["HTTP_X_FORWARDED"];
    } elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
        return $_SERVER["HTTP_FORWARDED_FOR"];
    } elseif (isset($_SERVER["HTTP_FORWARDED"])) {
        return $_SERVER["HTTP_FORWARDED"];
    } else {
        return $_SERVER["REMOTE_ADDR"];
    }
}


// Cuando el plugin se active se crea la tabla para recoger los datos si no existe
register_activation_hook(__FILE__, 'Kfp_Aspirante_init');

/**
 * Crea la tabla para recoger los datos del formulario
 *
 * @return void
 */
function Kfp_Aspirante_init()
{
    global $wpdb; // Este objeto global permite acceder a la base de datos de WP
    // Crea la tabla sólo si no existe
    // Utiliza el mismo prefijo del resto de tablas
    $tabla_aspirantes = $wpdb->prefix . 'aspirante';
    // Utiliza el mismo tipo de orden de la base de datos
    $charset_collate = $wpdb->get_charset_collate();
    // Prepara la consulta
    $query = "CREATE TABLE IF NOT EXISTS $tabla_aspirantes (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nombre varchar(40) NOT NULL,
        correo varchar(100) NOT NULL,
        nivel_html smallint(4) NOT NULL,
        nivel_css smallint(4) NOT NULL,
        nivel_js smallint(4) NOT NULL,
        aceptacion smallint(4) NOT NULL,
        ip_origen varchar(40) NOT NULL,
        created_at datetime NOT NULL,
        UNIQUE (id)
        ) $charset_collate;";
    // La función dbDelta permite crear tablas de manera segura se
    // define en el archivo upgrade.php que se incluye a continuación
    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($query); // Lanza la consulta para crear la tabla de manera segura
}

// Define el shortcode y lo asocia a una función
add_shortcode('formulario', 'Kfp_Aspirante_form');


//Carga hoja javascript
add_action("wp_enqueue_scripts", "dcms_insertar_js");

function dcms_insertar_js()
{
    wp_register_script('miscript', plugins_url('formulario.js', __FILE__), array('jquery'), '1', true);
    wp_enqueue_script('miscript');
}

/** 
 * Define la función que ejecutará el shortcode
 * De momento sólo pinta un formulario que no hace nada
 * 
 * @return string
 */
function Kfp_Aspirante_form()
{
    // Carga esta hoja de estilo para poner más bonito el formulario
    wp_enqueue_style('css_aspirante', plugins_url('formulario.css', __FILE__));

    global $wpdb; // Este objeto global permite acceder a la base de datos de WP
    // Si viene del formulario  graba en la base de datos
    // Cuidado con el último igual de la condición del if que es doble
    if (
        $_POST['nombre'] != ''
        and is_email($_POST['correo'])
        and $_POST['nivel_html'] != ''
        and $_POST['nivel_css'] != ''
        and $_POST['nivel_js'] != ''
        and $_POST['aceptacion'] == '1'
        and wp_verify_nonce($_POST['aspirante_nonce'], 'graba_aspirante')
    ) {
        $tabla_aspirantes = $wpdb->prefix . 'aspirante';
        $nombre = sanitize_text_field($_POST['nombre']);
        $correo = $_POST['correo'];
        $nivel_html = (int) $_POST['nivel_html'];
        $nivel_css = (int) $_POST['nivel_css'];
        $nivel_js = (int) $_POST['nivel_js'];
        $aceptacion = (int) $_POST['aceptacion'];
        $created_at = date('Y-m-d H:i:s');
        $ip_origen = getRealIP();
        $wpdb->insert(
            $tabla_aspirantes,
            array(
                'nombre' => $nombre,
                'correo' => $correo,
                'nivel_html' => $nivel_html,
                'nivel_css' => $nivel_css,
                'nivel_js' => $nivel_js,
                'aceptacion' => $aceptacion,
                'ip_origen' => $ip_origen,
                'created_at' => $created_at,
            )
        );
        echo "<p class='exito'><b>Tus datos han sido registrados</b>. Gracias 
            por tu interés. En breve contactaré contigo.<p>";
    }

    // Esta función de PHP activa el almacenamiento en búfer de salida (output buffer)
    // Cuando termine el formulario lo imprime con la función ob_get_clean
    ob_start();
?>
    <form action="<?php get_the_permalink(); ?>" method="post" id="form_aspirante" class="cuestionario">
        <?php wp_nonce_field('graba_aspirante', 'aspirante_nonce'); ?>
        <div class="form-input">
            <label for="nombre">Nombre</label>
            <input type="text" name="nombre" id="nombre" required>
        </div>
        <div class="form-input">
            <label for='correo'>Correo</label>
            <input type="email" name="correo" id="correo" required>
        </div>
        <div class="form-input">
            <label for="nivel_html">¿Cuál es tu nivel de HTML?</label>
            <input type="radio" name="nivel_html" value="1" required> Nada
            <br><input type="radio" name="nivel_html" value="2" required> Estoy
            aprendiendo
            <br><input type="radio" name="nivel_html" value="3" required> Tengo
            experiencia
            <br><input type="radio" name="nivel_html" value="4" required> Lo
            domino al dedillo
        </div>
        <div class="form-input">
            <label for="nivel_css">¿Cuál es tu nivel de CSS?</label>
            <input type="radio" name="nivel_css" value="1" required> Nada
            <br><input type="radio" name="nivel_css" value="2" required> Estoy
            aprendiendo
            <br><input type="radio" name="nivel_css" value="3" required> Tengo
            experiencia
            <br><input type="radio" name="nivel_css" value="4" required> Lo
            domino al dedillo
        </div>
        <div class="form-input">
            <label for="nivel_js">¿Cuál es tu nivel de JavaScript?</label>
            <input type="radio" name="nivel_js" value="1" required> Nada
            <br><input type="radio" name="nivel_js" value="2" required> Estoy
            aprendiendo
            <br><input type="radio" name="nivel_js" value="3" required> Tengo
            experiencia
            <br><input type="radio" name="nivel_js" value="4" required> Lo domino
            al dedillo
        </div>
        <div class="form-input">
            <label for="aceptacion">La información facilitada se tratará
                con respeto y admiración.</label>
            <input type="checkbox" id="aceptacion" name="aceptacion" value="1" required><a id="privacidad" target="_blank" href="http://www.google.com"> Entiendo y acepto las condiciones</a>
        </div>
        <div class="form-input">
            <input type="submit" id="btnFormulario" value="Enviar" disabled="true" title="Es necesario aceptar las condiciones">
        </div>
    </form>
    <!--
    <script>
        window.onload = () => {
            document.getElementById("privacidad").onclick=(e)=>{
                e.preventDefault;
                alert("Aceptando condiciones");
            }
           
        }
    </script>
    -->
<?php

    // Devuelve el contenido del buffer de salida
    return ob_get_clean();
}

// El hook "admin_menu" permite agregar un nuevo item al menú de administración
add_action("admin_menu", "Kfp_Aspirante_menu");

/**
 * Agrega el menú del plugin al escritorio de WordPress
 *
 * @return void
 */
function Kfp_Aspirante_menu()
{
    add_menu_page(
        'Formulario Aspirantes',
        'Datos Recibidos',
        'manage_options',
        'kfp_aspirante_menu',
        'Kfp_Aspirante_admin',
        'dashicons-editor-paste-word',
        1
    );
}

/**
 * Crea el contenido del panel de administración para el plugin
 *
 * @return void
 */
function Kfp_Aspirante_admin()
{
    // Carga esta hoja de estilo para poner más bonito el formulario
    wp_enqueue_style('css_aspirante', plugins_url('formulario.css', __FILE__));

    global $wpdb;
    $tabla_aspirantes = $wpdb->prefix . 'aspirante';
    $consulta = "SELECT * FROM $tabla_aspirantes";
    if (isset($_POST["nombre_buscar"])) {
        echo "Recibiendo datos";
        $nombre = $_POST["nombre_buscar"];
        $consulta = "SELECT * FROM $tabla_aspirantes where nombre like '%$nombre%'";
    }
    if (isset($_POST["nombre"])) {
        $tabla_aspirantes = $wpdb->prefix . 'aspirante';
        $nombre = sanitize_text_field($_POST['nombre']);
        $correo = $_POST['correo'];
        $nivel_html = (int) $_POST['nivel_html'];
        $nivel_css = (int) $_POST['nivel_css'];
        $nivel_js = (int) $_POST['nivel_js'];
        $created_at = date('Y-m-d H:i:s');
        $wpdb->insert(
            $tabla_aspirantes,
            array(
                'nombre' => $nombre,
                'correo' => $correo,
                'nivel_html' => $nivel_html,
                'nivel_css' => $nivel_css,
                'nivel_js' => $nivel_js,
                'aceptacion' => 1,
                'ip_origen' => 'admin',
                'created_at' => $created_at,
            )
        );
    }
    if (isset($_GET["id"])) {
        $id_aspirante = $_GET["id"];
        // Using where formatting.
        $wpdb->delete($tabla_aspirantes, array('ID' => $id_aspirante), array('%d'));
        //echo "Borrando aspirante id " . $id_aspirante;
        //exit();
    }

    echo '<div class="wrap"><h1>Lista de aspirantes</h1>';
    echo '<hr>';
    echo '<form action="" method="post">
     <input type="text" ';
    if (isset($_POST["nombre_buscar"])) {
        echo "value=" . $nombre;
    }
    echo ' required name="nombre_buscar" id="" placeholder="Nombre de usuario">
     <input type="submit" value="Buscar">
    </form><br>';

    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th width="10%">Nombre</th><th width="20%">Correo</th>
        <th>HTML</th><th>CSS</th><th>JS</th>
        <th>PHP</th><th>WP</th><th>Total</th><th width="15%">IP Origen</th><th>Op</th></tr></thead>';
    echo '<tbody id="the-list">';
    $aspirantes = $wpdb->get_results($consulta);
    foreach ($aspirantes as $aspirante) {
        $id = (int) $aspirante->id;
        $nombre = esc_textarea($aspirante->nombre);
        $correo = esc_textarea($aspirante->correo);
        $motivacion = esc_textarea($aspirante->motivacion);
        $nivel_html = (int) $aspirante->nivel_html;
        $nivel_css = (int) $aspirante->nivel_css;
        $nivel_js = (int) $aspirante->nivel_js;
        $nivel_php = (int) $aspirante->nivel_php;
        $nivel_wp = (int) $aspirante->nivel_wp;
        $total = $nivel_html + $nivel_css + $nivel_js + $nivel_php + $nivel_wp;
        $ip_origen = $aspirante->ip_origen;
        echo "<tr><td><a href='#' title='$motivacion'>$nombre</a></td>
            <td>$correo</td><td>$nivel_html</td><td>$nivel_css</td>
            <td>$nivel_js</td><td>$nivel_php</td><td>$nivel_wp</td>
            <td>$total</td><td>$ip_origen</td><td><a href='?page=kfp_aspirante_menu&id=$id'><span class='dashicons dashicons-trash'></span></a></td></tr>";
    }
    echo '</tbody></table></div>';
    echo '<hr>';

    echo '<form action="" method="post" id="form_aspirante" class="cuestionario">        
        <div class="form-input">
            <label for="nombre">Nombre</label>
            <input type="text" name="nombre" id="nombre" required>
        </div>
        <div class="form-input">
            <label for="correo">Correo</label>
            <input type="email" name="correo" id="correo" >
        </div>
        <div class="form-input">
            <label for="nivel_html">¿Cuál es tu nivel de HTML?</label>
            <input type="radio" name="nivel_html" value="1" > Nada
            <br><input type="radio" name="nivel_html" value="2" > Estoy
            aprendiendo
            <br><input type="radio" name="nivel_html" value="3" > Tengo
            experiencia
            <br><input type="radio" name="nivel_html" value="4" > Lo
            domino al dedillo
        </div>
        <div class="form-input">
            <label for="nivel_css">¿Cuál es tu nivel de CSS?</label>
            <input type="radio" name="nivel_css" value="1" > Nada
            <br><input type="radio" name="nivel_css" value="2" > Estoy
            aprendiendo
            <br><input type="radio" name="nivel_css" value="3" > Tengo
            experiencia
            <br><input type="radio" name="nivel_css" value="4" > Lo
            domino al dedillo
        </div>
        <div class="form-input">
            <label for="nivel_js">¿Cuál es tu nivel de JavaScript?</label>
            <input type="radio" name="nivel_js" value="1" > Nada
            <br><input type="radio" name="nivel_js" value="2" > Estoy
            aprendiendo
            <br><input type="radio" name="nivel_js" value="3" > Tengo
            experiencia
            <br><input type="radio" name="nivel_js" value="4" > Lo domino
            al dedillo
        </div>
        
        <div class="form-input">
            <input type="submit" id="btnFormulario" value="Guardar datos"  title="Es necesario aceptar las condiciones">
        </div>
    </form>';
}


function events_endpoint()
{
    register_rest_route('eventos/', 'destacados', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'get_events',
    ));
}

add_action('rest_api_init', 'events_endpoint');

function get_events($request)
{
    $args  = array(
        'post_type'  => 'blog'

    );
    $query = new WP_Query(array('cat' => 1));

    return $query->posts;
    //return "hola mundo";
}
