

var express = require('express');
var path = require('path');
var favicon = require('serve-favicon');
var logger = require('morgan');
var cookieParser = require('cookie-parser');
var bodyParser = require('body-parser');
var low = require('lowdb');
var session = require('express-session');
var tozny = require('tozny-auth');

var routes = require('./routes/index');
var users = require('./routes/user');

require('dotenv').config();

var app = express();
app.set('trust proxy', 1);
app.use(session({
    secret: 'netmagazine',
    resave: false,
    saveUninitialized: true,
    cookie: {}
}));

var env = process.env.NODE_ENV || 'development';
app.locals.ENV = env;
app.locals.ENV_DEVELOPMENT = env == 'development';

// Dependencies
app.db = low('db.json');
app.db.defaults({ users: [] }).value();
app.hasher = require('bcrypt');

app.tozny_realm = new tozny.Realm(
    process.env.REALM_KEY_ID,
    process.env.REALM_SECRET,
    process.env.TOZNY_API
);
app.tozny_user = new tozny.User(
    process.env.REALM_KEY_ID,
    process.env.TOZNY_API
);

// Messages
app.errors = {
    'generic'     : 'Something went wrong ... please contact support',
    'useduser'    : 'That username is already in use',
    'emptyemail'  : 'Please enter a valid email address',
    'badpassword' : 'Your current password is invalid',
    'nomatch'     : 'Please re-enter the same password to confirm',
    'invalidlogin': 'Invalid login. Perhaps you need to register first.',
    'notloggedin' : 'You don\'t seem to be logged in'
};
app.messages = {
    'registered': 'Your account is now registered!',
    'checkemail': 'Please check your email for a magic link.',
    'loggedout' : 'You have been logged out'
};

// view engine setup

app.set('views', path.join(__dirname, 'views'));
app.set('view engine', 'jade');

// app.use(favicon(__dirname + '/public/img/favicon.ico'));
app.use(logger('dev'));
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({
  extended: true
}));
app.use(cookieParser());
app.use(express.static(path.join(__dirname, 'public')));

app.use('/', routes);
app.use('/users', users);

/// catch 404 and forward to error handler
app.use(function(req, res, next) {
    var err = new Error('Not Found');
    err.status = 404;
    next(err);
});

/// error handlers

// development error handler
// will print stacktrace

if (app.get('env') === 'development') {
    app.use(function(err, req, res, next) {
        res.status(err.status || 500);
        res.render('error', {
            message: err.message,
            error: err,
            title: 'error'
        });
    });
}

// production error handler
// no stacktraces leaked to user
app.use(function(err, req, res, next) {
    res.status(err.status || 500);
    res.render('error', {
        message: err.message,
        error: {},
        title: 'error'
    });
});


module.exports = app;
