<?php

return [

    /**
     * 
     * Colors
     * 
     * You can change these values to modify the style of Katra to better 
     * match your organization. We have set some defaults for you, but
     * you are free to change them to whatever you'd like!
     * 
     */

    'colors' => [

        /**
         * 
         * Primary Color
         * 
         * This will be the most obvious change you can make to Katra.
         * Use it wisely, unless your user base only uses screen
         * readers... In which case, go nuts!
         * 
         */
        'primary' => '#6201EE',

        /**
         * 
         * Text Color
         * 
         * If your primary color is too bright, you may not be able to read 
         * white text on top of it. Set `text` to `dark` and you should
         * be able to read the text in those scenarios again.
         * 
         */

        // 'text' => 'dark',
    ],

    /**
     * 
     * Users
     * 
     * Who likes new features? Users like new features! Who
     * submits bug reports when things don't work? Stake-
     * holders do. Save yourself the trouble, and ban 
     * them early! You'll thank me later!
     * 
     */

    'users' => [
        
        /**
         * 
         * User Alias
         * 
         * Many apps are rude and call your users... well, `users`. But 
         * with Katra, you can be more polite or even ruder! We must
         * encourage you to call them nice things; like a `friend`
         * But you are free to call them an `asshole`. Just use 
         * the singular version, we will be sure pluralize 
         * and capitalize this where ever it's needed!
         * 
         */
        
        'alias' => 'user',
    ],

    /**
     * 
     * Routing
     * 
     * Here, you can adjust the routing configuration. Want to force 
     * your employees/co-workers to type things like: `i-eat-ass` 
     * to access the tools they need to work? We don't advise
     * it... but we do support your decisions! #freedom
     * 
     */

    'route' => [

        /**
         * 
         * Route Prefix
         * 
         * If you could use Katra on any path you choose, what
         * would it be? We like `/admin`, but you crazy kids
         * are welcome to set this to anything (... that 
         * is not already taken in your application)
         * 
         */

        'prefix' => 'admin',

        /**
         * 
         * Route Middleware
         * 
         * To be honest, I am not sure why I added this to the 
         * config file. It seemed like a good idea at the 
         * time... Change this at your own risk!
         * 
         * FYI - removing `web` breaks things...
         * 
         */

        'middleware' => ['web'],
    ],
];