'use strict';

var express = require('express');
var router = express.Router();

/* GET home page. */

router.get('/', function(req, res) {
  let data = { title: 'Net Magazine Magic Link Demo' };

  if (req.param('error') !== undefined ) {
    data.error = req.app.errors[ req.param('error') ];
  }
  if (req.param('message') !== undefined ) {
    data.message = req.app.messages[ req.param('message') ];
  }

  res.render('index', data);
});

router.get('/register', function(req, res) {
  let data = { title: 'Net Magazine Magic Link Demo' };

  if (req.param('error') !== undefined ) {
    data.error = req.app.errors[ req.param('error') ];
  }
  if (req.param('message') !== undefined ) {
    data.message = req.app.messages[ req.param('message') ];
  }

  res.render('register', data);
});
router.post('/register', function(req, res) {
  let fname = req.param('fname');
  let lname = req.param('lname');
  let username = req.param('username');
  let email = req.param('email');
  let password = req.param('password');
  let cpassword = req.param('password_confirm');

  if (req.app.db.get('users').find({username: username}).value()) {
    return res.redirect('/register?error=useduser');
  }
  if (undefined == email || 0 == email.length) {
    return res.redirect('/register?error=emptyemail');
  }
  if (password != cpassword) {
    return res.redirect('/register?error=nomatch');
  }

  let user = {
    'firstName': fname,
    'lastName': lname,
    'username': username,
    'email': email,
    'password': req.app.hasher.hashSync(password, 8)
  };

  req.app.db.get('users').push(user).value();

  req.session.username = username;
  req.session.save();
console.log(req.session);
  return res.redirect('/authenticated');
});

router.all('/login', function(req, res) {
  if ( undefined !== req.body.magiclink ) {
    let username = req.body.username;

    if (undefined !== username) {
      let user = req.app.db.get('users').find({username: username}).value();

      if (user) {
        return req.app.tozny_realm.linkChallenge(
            user.email,
            process.env.SITEURL + '/login',
            undefined,
            'authenticate',
            true,
            JSON.stringify( {'username': username})
        ).then(function(sent) {
          if ( 'ok' === sent.return ) {
            return res.redirect('/?message=checkemail');
          }
        });
      }
    }
  }

  return res.redirect('/?error=invalidlogin');
});

router.get('/authenticated', function(req, res) {
  let data = { title: 'Net Magazine Magic Link Demo' };

  if (undefined == req.session.username) {
    return res.redirect('/?error=notloggedin');
  }

  let user = req.app.db.get('users').find({username: req.session.username}).value();
  data.fName = user.firstName;
  data.lName = user.lastName;

  return res.render('authenticated', data);
});

router.get('/logout', function(req, res) {
  req.session.destroy();

  return res.redirect('/?loggedout');
});

module.exports = router;
