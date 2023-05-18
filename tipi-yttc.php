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

                // Check if neither the start nor end date is before or the same as the current moment
                if (strtotime($start_date) > time() || strtotime($end_date) > time()) {
                    // Add the sanitized date pair to the array
                    $sanitized_dates[] = array(
                        "start_date" => $start_date,
                        "end_date" => $end_date
                    );
                }
            }

            // Sort the dates by start date in ascending order
            usort($sanitized_dates, function ($a, $b) {
                return strtotime($a['start_date']) - strtotime($b['start_date']);
            });

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
                    $pairIndex = 0;
                    foreach ($dates as $date_pair) {
                        $start_date = esc_attr($date_pair['start_date']);
                        $end_date = esc_attr($date_pair['end_date']);

                        echo '<div class="tipi-yttc-date-pair">';
                        echo '<label>Pair ' . ($pairIndex + 1) . '</label>';
                        echo '<input type="date" name="tipi-yttc-dates[' . $pairIndex . '][start_date]" value="' . $start_date . '" />';
                        echo ' - ';
                        echo '<input type="date" name="tipi-yttc-dates[' . $pairIndex . '][end_date]" value="' . $end_date . '" />';
                        echo '<button type="button" class="button tipi-yttc-delete-date">Delete</button>';
                        echo '</div>';

                        $pairIndex++;
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
        var datesContainer = document.querySelector(".tipi-yttc-dates-container");

        datesContainer.addEventListener("click", function(event) {
            if (event.target.classList.contains("tipi-yttc-delete-date")) {
                var datePair = event.target.closest('.tipi-yttc-date-pair');
                datePair.remove();
            }
        });

        var addButton = document.getElementById("tipi-yttc-add-date");
        var pairIndex = datesContainer.querySelectorAll(".tipi-yttc-date-pair").length;

        if (addButton) {
            addButton.addEventListener("click", function() {
                var newDatePair = document.createElement("div");
                newDatePair.classList.add("tipi-yttc-date-pair");

                newDatePair.innerHTML = `
                    <label>Pair ${pairIndex + 1}</label>
                    <input type="date" name="tipi-yttc-dates[${pairIndex}][start_date]" value="" />
                    -
                    <input type="date" name="tipi-yttc-dates[${pairIndex}][end_date]" value="" />
                    <button type="button" class="button tipi-yttc-delete-date">Delete</button>
                `;

                datesContainer.appendChild(newDatePair);
                pairIndex++;
            });
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
    echo '<div class="tipi-yttc-date-fields">';
    $pairIndex = 0;
    foreach ($dates as $date_pair) {
        $start_date = esc_attr($date_pair['start_date']);
        $end_date = esc_attr($date_pair['end_date']);

        echo '<div class="tipi-yttc-date-pair">';
        echo '<label>Pair ' . ($pairIndex + 1) . '</label>';
        echo '<input type="date" name="tipi-yttc-dates[' . $pairIndex . '][start_date]" value="' . $start_date . '" />';
        echo ' - ';
        echo '<input type="date" name="tipi-yttc-dates[' . $pairIndex . '][end_date]" value="' . $end_date . '" />';
        echo '<button type="button" class="button tipi-yttc-delete-date">Delete</button>';
        echo '</div>';

        $pairIndex++;
    }
    echo '</div>';
}

// Output shortcode
function future_yttc_shortcode($atts) {
    $dates = get_option('tipi-yttc-dates', array());
    $limit = isset($atts['limit']) ? intval($atts['limit']) : 3; // Limit the number of displayed dates

    $output = '<ul>';

    $count = 0; // Counter to track the number of displayed dates

    foreach ($dates as $date_pair) {
        $start_date = date('M j', strtotime($date_pair['start_date'])); // Format the start date as "M j"
        $end_date = date('M j', strtotime($date_pair['end_date'])); // Format the end date as "M j"
        $year = date('Y', strtotime($date_pair['start_date'])); // Extract the year from the start date

        $output .= '<li><span class="date">' . $start_date . ' - ' . $end_date . ' ' . $year . '</span></li>';

        $count++; // Increment the counter

        // Break the loop if the number of displayed dates reaches the limit
        if ($count >= $limit) {
            break;
        }
    }

    $output .= '</ul>';

    return $output;
}
add_shortcode('future-yttc', 'future_yttc_shortcode');

// Output shortcode
function next_yttc_shortcode($atts) {
    $dates = get_option('tipi-yttc-dates', array());

    // Find the next date pair
    $next_date = null;
    $current_date = new DateTime('today');
    foreach ($dates as $date_pair) {
        $start_date = new DateTime($date_pair['start_date']);
        $end_date = new DateTime($date_pair['end_date']);

        if ($start_date > $current_date) {
            $next_date = $date_pair;
            break;
        }
    }

    $output = '';

    if ($next_date) {
        $start_date = date('F j', strtotime($next_date['start_date']));
        $end_date = date('F j, Y', strtotime($next_date['end_date']));
        $output = $start_date . ' - ' . $end_date;
    }

    return $output;
}
add_shortcode('next-yttc', 'next_yttc_shortcode');


function next_and_upcoming_yttcs_shortcode($atts) {
    $dates = get_option('tipi-yttc-dates', array());

    // Find the next date pair
    $next_date = null;
    $current_date = new DateTime('today');
    foreach ($dates as $date_pair) {
        $start_date = new DateTime($date_pair['start_date']);
        $end_date = new DateTime($date_pair['end_date']);

        if ($start_date >= $current_date) {
            $next_date = $date_pair;
            break;
        }
    }

    $output = '';

    if ($next_date) {
        // Format the next date pair
        $next_start_date = date('F j', strtotime($next_date['start_date']));
        // $next_end_date = date('F j', strtotime($next_date['end_date']));
        $next_end_date_with_year = date('F j Y', strtotime($next_date['end_date']));
        $next_output = '<h2 style="text-decoration: underline;">' . $next_start_date . ' - ' . $next_end_date_with_year . '</h2>';

        // Find the upcoming date pairs
        $upcoming_dates = array_slice($dates, array_search($next_date, $dates) + 1, 2);
        $upcoming_output = '';
        foreach ($upcoming_dates as $date_pair) {
            $start_date = date('M j', strtotime($date_pair['start_date']));
            $end_date_with_year = date('M j Y', strtotime($date_pair['end_date']));
            $upcoming_output .= $start_date . ' - ' . $end_date_with_year . '<br />';
        }

        // Generate the final output
        if ($upcoming_output) {
            $output = $next_output . '<h3><strong>Other Course Dates</strong></br>' . $upcoming_output . '</h3>';
        } else {
            $output = $next_output;
        }
    }

    return $output;
}
add_shortcode('next-and-upcoming-yttcs', 'next_and_upcoming_yttcs_shortcode');



function upcoming_yttcs_shortcode($atts) {// need to strip the first date off this one still
    $dates = get_option('tipi-yttc-dates', array());
    $next_date = '';

    foreach ($dates as $date_pair) {
        $start_date = new DateTime($date_pair['start_date']);

        if ($start_date > new DateTime('today')) {
            $next_date = $date_pair;
            break;
        }
    }

    $output = '';

    if (!empty($next_date)) {
        $start_date = date('F j', strtotime($next_date['start_date']));
        $end_date = date('F j', strtotime($next_date['end_date']));
        $year = date('Y', strtotime($next_date['start_date']));

        $output = $start_date . ' - ' . $end_date . ' ' . $year;
    }

    return $output;
}

add_shortcode('upcoming-yttcs', 'upcoming_yttcs_shortcode');
