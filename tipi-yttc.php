<?php
/*
Plugin Name: Tipi YTTC
Description: Makes a list of dates for YTTC. Access from Settings->Tipi YTTC... Shortcode is tipi-yttc-dates
Version: 1.0
Author: Your Name
Author URI: Your Website
*/

// Add menu item under Settings for Tipi YTTC
function tipi_yttc_add_menu_item() {
    add_options_page('Tipi YTTC Settings', 'Tipi YTTC', 'manage_options', 'tipi-yttc-settings', 'tipi_yttc_settings_page');
}
add_action('admin_menu', 'tipi_yttc_add_menu_item');

// Display the settings page
function tipi_yttc_settings_page() {
    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["tipi-yttc-dates"])) {
            $dates = $_POST["tipi-yttc-dates"];

            // Sanitize and validate the dates
            $sanitized_dates = array();
            foreach ($dates as $date_pair) {
                $start_date = sanitize_text_field($date_pair["start_date"]);
                $end_date = sanitize_text_field($date_pair["end_date"]);

                // Perform validation if required

                // Add the sanitized date pair to the array
                $sanitized_dates[] = array(
                    "start_date" => $start_date,
                    "end_date" => $end_date
                );
            }

            // Save the dates to the database
            update_option("tipi-yttc-dates", $sanitized_dates);
        }
    }

    // Retrieve the saved dates
    $dates = get_option("tipi-yttc-dates", array());

    ?>
    <div class="wrap">
        <h1>Tipi YTTC Settings</h1>

        <div id="tipi-yttc-settings-form">
            <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
                <?php
                settings_fields('tipi-yttc-settings');
                ?>
                <h2 class="title">Start and End Dates</h2>
                <?php
                do_action('tipi-yttc-settings-section');
                ?>
                <table class="form-table">
                    <?php
                    do_action('tipi-yttc-settings-fields', $dates);
                    ?>
                </table>
                <div class="tipi-yttc-dates-container">
                    <?php
                    foreach ($dates as $index => $date_pair) {
                        $start_date = esc_attr($date_pair['start_date']);
                        $end_date = esc_attr($date_pair['end_date']);

                        echo '<div class="tipi-yttc-date-pair">';
                        echo '<label>Pair ' . ($index + 1) . '</label>';
                        echo '<input type="date" name="tipi-yttc-dates[' . $index . '][start_date]" value="' . $start_date . '" />';
                        echo ' - ';
                        echo '<input type="date" name="tipi-yttc-dates[' . $index . '][end_date]" value="' . $end_date . '" />';
                        echo '<button type="button" class="button tipi-yttc-delete-date">Delete</button>';
                        echo '</div>';
                    }
                    ?>
                </div>
                <?php
                submit_button('Save Changes');
                ?>
            </form>

            <button type="button" class="button" id="tipi-yttc-add-date">Add Date Pair</button>

            <script>
    document.addEventListener("DOMContentLoaded", function() {
        var addButton = document.getElementById("tipi-yttc-add-date");
        var datesContainer = document.querySelector(".tipi-yttc-dates-container");

        var pairIndex = datesContainer.querySelectorAll(".tipi-yttc-date-pair").length + 1;

        if (addButton) {
            addButton.addEventListener("click", function() {
                var newDatePair = document.createElement("div");
                newDatePair.classList.add("tipi-yttc-date-pair");

                newDatePair.innerHTML = `
                    <label>Pair ${pairIndex}</label>
                    <input type="date" name="tipi-yttc-dates[${pairIndex}][start_date]" value="" />
                    -
                    <input type="date" name="tipi-yttc-dates[${pairIndex}][end_date]" value="" />
                    <button type="button" class="button tipi-yttc-delete-date">Delete</button>
                `;

                datesContainer.appendChild(newDatePair);
                pairIndex++;
            });
        }

        datesContainer.addEventListener("click", function(event) {
            if (event.target.classList.contains("tipi-yttc-delete-date")) {
                var datePair = event.target.closest('.tipi-yttc-date-pair');
                datePair.remove();
            }
        });

        // Ensure only one date pair is added on page load
        var datePairs = datesContainer.querySelectorAll(".tipi-yttc-date-pair");
        if (datePairs.length === 0) {
            var newDatePair = document.createElement("div");
            newDatePair.classList.add("tipi-yttc-date-pair");

            newDatePair.innerHTML = `
                <label>Pair 1</label>
                <input type="date" name="tipi-yttc-dates[0][start_date]" value="" />
                -
                <input type="date" name="tipi-yttc-dates[0][end_date]" value="" />
                <button type="button" class="button tipi-yttc-delete-date">Delete</button>
            `;

            datesContainer.appendChild(newDatePair);
        }
    });
</script>


        </div>
    </div>
    <?php
}

// Register settings and fields
function tipi_yttc_register_settings() {
    register_setting('tipi-yttc-settings', 'tipi-yttc-dates');
    add_action('tipi-yttc-settings-section', 'tipi_yttc_dates_section');
    // add_action('tipi-yttc-settings-fields', 'tipi_yttc_dates_field');
}
add_action('admin_init', 'tipi_yttc_register_settings');

// Render the dates section
function tipi_yttc_dates_section() {
    echo '<p>Start and end date pairs:</p>';
}

// Render the dates fields
function tipi_yttc_dates_field($dates) {
    // echo '<div class="tipi-yttc-date-fields">';
    // foreach ($dates as $index => $date_pair) {
    //     $start_date = esc_attr($date_pair['start_date']);
    //     $end_date = esc_attr($date_pair['end_date']);

    //     echo '<div class="tipi-yttc-date-pair">';
    //     echo '<label>Pair ' . ($index + 1) . '</label>';
    //     echo '<input type="date" name="tipi-yttc-dates[' . $index . '][start_date]" value="' . $start_date . '" />';
    //     echo ' - ';
    //     echo '<input type="date" name="tipi-yttc-dates[' . $index . '][end_date]" value="' . $end_date . '" />';
    //     echo '<button type="button" class="button tipi-yttc-delete-date">Delete</button>';
    //     echo '</div>';
    // }
    // echo '</div>';
}

// Output shortcode
function tipi_yttc_shortcode($atts) {
    $dates = get_option('tipi-yttc-dates', array());

    $output = '<ul>';

    foreach ($dates as $date_pair) {
        $start_date = date('M d', strtotime($date_pair['start_date']));
        $end_date = date('M d', strtotime($date_pair['end_date']));

        $output .= '<li><span class="startdate">' . $start_date . '</span> - <span class="enddate">' . $end_date . '</span></li>';
    }

    $output .= '</ul>';

    return $output;
}
add_shortcode('tipi-yttc-dates', 'tipi_yttc_shortcode');
