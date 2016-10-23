var express = require('express');
var router = express.Router();

/* GET home page. */

router.get('/', function(req, res) {
  res.render('index', { title: 'Net Magazine Magic Link Demo' });
});

module.exports = router;
