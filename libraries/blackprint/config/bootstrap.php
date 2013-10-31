<?php
/**
 * This file contains configuration for cache. Most likely the main application
 * will also have a similar file but, just in case, Blackprint creates its own
 * configuration to use.
 */
require __DIR__ . '/bootstrap/cache.php';

/**
 * The connections file includes various database connection configurations
 * from the main application's config/connections directory. To allow for
 * a flexible configuration without Blackprint making any assumptions.
 */
require __DIR__ . '/bootstrap/connections.php';

/**
 * Load the configuration for Blackprint from the database.
 * Obviously the database configuration can't come from here, but there
 * are a bunch of configuration options that can be stored in the database
 * and used during bootstrap.
*/
require __DIR__ . '/bootstrap/config.php';

/**
 * The libraries file contains the loading instructions for all plugins, frameworks and other class
 * libraries used in the application, including the Lithium core, and the application itself. These
 * instructions include library names, paths to files, and any applicable class-loading rules. This
 * file can also statically loads common classes to improve bootstrap performance.
 */
require __DIR__ . '/bootstrap/libraries.php';

/**
 * The error configuration allows you to use the filter system along with the advanced matching
 * rules of the `ErrorHandler` class to provide a high level of control over managing exceptions in
 * your application, with no impact on framework or application code.
 */
require __DIR__ . '/bootstrap/errors.php';

/**
 * This file contains configuration for session (and/or cookie) storage, and user or web service
 * authentication.
 */
require __DIR__ . '/bootstrap/session.php';

/**
 * This file contains configurations for handling different content types within the framework,
 * including converting data to and from different formats, and handling static media assets.
 */
require __DIR__ . '/bootstrap/media.php';

/**
 * This file contains confiuration for logging.
 * Log files will be written to /resources/tmp/logs
 */
require __DIR__ . '/bootstrap/logging.php';

/**
 * The default Blackprint static menu.
 * Is loaded from the BlackprintMenu model...But could be extended.
 * This file shows an example for how to do that.
*/
// require __DIR__ . '/bootstrap/menu.php';

/**
 * Default Blackprint authentication config.
*/
require __DIR__ . '/bootstrap/auth.php';

/**
 * Default, restrictive, Blackprint access rules and checks.
 * Blackprint uses Lithium's filter system to apply access control
 * checks in order to remove the process completely from controllers, etc.
*/
require __DIR__ . '/bootstrap/access.php';

/**
 * Communications bootstrap, which allows the system to send/receive messages.
*/
require __DIR__ . '/bootstrap/communications.php';

/**
 * Last, but not least, the template override filter.
 * It allows you to override any library's templates with your own.
 * This way, you can take any given library that may contain the 
 * functionality you want and simply redesign it to fit your app
 * without modifying the templates in the library.
 * 
 * You do this through configuration options passed when the 
 * library is added. By deafult it will first look for templates
 * in the `views/_libraries/library_name` and then 
 * `views/_libraries/layouts/library_name`directories.
 * 
 * Don't forget, if the library requires extensive modification to
 * work with your application, you can always branch it =)
 */
require __DIR__ . '/bootstrap/templates.php';
?>