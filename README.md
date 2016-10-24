Net Magazine Magic Links Demo
=============================

The average person had [90 different online accounts](https://blog.dashlane.com/infographic-online-overload-its-worse-than-you-thought/), but only maintains [19 distinct passwords]( https://nakedsecurity.sophos.com/2014/10/17/average-person-has-19-passwords-but-1-in-3-dont-make-them-strong-enough/).

TL;RD: we’re lazy.

Chances are high that your users are either reusing a password or using one so simple a breach is only a matter of time. It’s your responsibility to keep your site’s data safe, but the weakest component of your site’s security is entirely out of your control.

With that said, why not eliminate passwords?

Authentication with Magic Links
-------------------------------

The point of this repository is to demonstrate how trivial it is to support _both_ password-based authentication and a more cutting-edge magic link workflow in the same application. To that end, there are implementations in both PHP (using [Slim](http://www.slimframework.com/)) and JavaScript (using [Express](http://expressjs.com/). Both implementations flesh out a simple app that presents:

- A login/homepage
- User registration
- A private page requiring authentication

It's a simple example, but presents the backbone required to support this workflow in any app.

Requirements
------------

Both versions of the application will require an account with [Tozny](https://tozny.com) as their platform powers the entire workflow. Signing up is free and helps you take the first step to securing your users' accounts beyond their ability to remember sophisticated password schemes.

Once you have a Tozny account, copy `.env.example` to `.env` in both project directories and replace the placeholder values with your real Realm Key ID and Secret.

The PHP version requires you use Composer to install dependencies:

```
$ cd slim && composer install
```

The JavaScript version requires you use NPM:

```
$ cd express && npm install
```

Running
-------

The `run-express.sh` and `run-slim.sh` scripts in the project root will start the respective projects running on internal servers. The PHP version loads as http://localhost:8080 and the JS version as http://localhost:3000. Both use internal, flat database structures for user credentials and strong hashing for the password-based workflows. _You will need an Internet connection_ to communicate with the Tozny API for both sending and validating magic links.

Questions
---------

While the application(s) have been pre-built for you, I'm more than willing to accept feedback, answer questions, or make improvements. Feel free to [open an issue](https://github.com/ericmann/netmag-magiclinks/issues) if you need anything at all.