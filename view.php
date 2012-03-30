<?PHP  // $Id: view.php,v 1.0 2012/03/28 18:30:00 Serafim Panov Exp $

/// This page prints a particular instance of etherpad
/// (Replace etherpad with the name of your module)

    require_once("../../config.php");
    require_once("lib.php");
    require_once("etherpad-lite-client.php");

    $id = optional_param('id', 0, PARAM_INT);    // Course Module ID
    $a  = optional_param('a', NULL, PARAM_TEXT);     // etherpad ID

    if ($id) {
        if (! $cm = $DB->get_record("course_modules", array("id" => $id))) {
            error("Course Module ID was incorrect");
        }
    
        if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
            error("Course is misconfigured");
        }
    
        if (! $etherpad = $DB->get_record("etherpad", array("id" => $cm->instance))) {
            error("Course module is incorrect");
        }

    } else {
        if (! $etherpad = $DB->get_record("etherpad", array("id" => $a))) {
            error("Course module is incorrect");
        }
        if (! $course = $DB->get_record("course", array("id" => $etherpad->course))) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("etherpad", $etherpad->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
    }

    require_login($course->id);
    
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

/// Print the page header

    $stretherpads = get_string("modulenameplural", "etherpad");
    $stretherpad  = get_string("modulename", "etherpad");

    
    if ($course->category) {
        $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
    } else {
        $navigation = '';
    }
    
    add_to_log($course->id, "etherpad", "view", "view.php?id=$id", "$cm->instance");
    
// Activate epad user
    //etherpad_activate_session();

// Initialize $PAGE, compute blocks
    $PAGE->set_url('/mod/etherpad/view.php', array('id' => $id));
    
    $title = $course->shortname . ': ' . format_string($etherpad->name);
    $PAGE->set_title($title);
    $PAGE->set_heading($course->fullname);

    echo $OUTPUT->header();
    
    echo '
    <script src="js/jquery.min.js"></script>
    <script src="js/etherpad.js"></script>
    ';

/// Print the main part of the page
    
    echo $OUTPUT->box_start('generalbox');
    
    echo "<div align=center>".$etherpad->intro."</div>";
    
    echo $OUTPUT->box_end();

    echo $OUTPUT->box_start('generalbox');
    
    echo "<div align=center>";
    
    $pname = etherpad_pad_prefix().$cm->instance;
    
    echo "
    
    <script type=\"text/javascript\">
$(document).ready(function() {
    $('#ePad').pad({'host': '{$etherpadcfg->etherpad_baseurl}', 'padId':'{$pname}', 'baseUrl': '/p/', 'showChat': true, 'userName': '{$USER->firstname} {$USER->lastname}','showControls': true,'showLineNumbers': true, 'height': 500});
});
</script>";
    
    echo '<div id="ePad"></div>';
    
    echo "</div>";
    
    echo $OUTPUT->box_end();

/// Finish the page
    echo $OUTPUT->footer();

