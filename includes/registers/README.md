Directory for registers
-----------------------
Naran boilerplace code (hereafter, CPBN. 'CPBN' is a term used call the project
itself, because every 'N-B-P-C' term is going to be replaced.) perfer explicitly defined sets of
core-registration-required objects.

Your plugin or theme will need custom post tyes, custom taxonomies, scripts, styles,
ajax calls or regular form submits, and so on. To make thoese features available, you need to
register them to the core.

* Activation: register_activation_hook()
* AJAX: add_action( 'wp_ajax_(nopriv_)....', 'handler' )
* Cron: wp_schedule_event(), or wp_schedule_single_event()
* Cron scheudle: add_filter( 'cron_schedules', 'handler' )
* Custom post type: register_post_type()
* Custom taxonomie: register_taxonomy()
* CSS: wp_register_style()
* Deactivation: register_deactivation_hook()
* JavaScript: wp_register_script()
* Meta: register_meta()
* Option: register_setting()
* Regular form subit: add_action( 'admin_post_(nopriv_)...', 'handler; )
* Shortcode: add_shortcode( 'shortcode', 'handler' )
* Uninstall

But the problem is, your 'registration' codes are spread up, and become hard to find.
So CPBN implemented register-registrable interface. All of your registration is defined, and
done followed by this interface.
