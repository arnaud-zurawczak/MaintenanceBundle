<div align="center">
<h2>AdyMaintenanceBundle</h2>

*This repository is forked from [LexikMaintenanceBundle](https://github.com/lexik/LexikMaintenanceBundle).*

[![pipeline status](https://gitlab.com/adynemo/maintenance-bundle/badges/master/pipeline.svg)](https://gitlab.com/adynemo/maintenance-bundle/-/commits/master)
[![coverage report](https://gitlab.com/adynemo/maintenance-bundle/badges/master/coverage.svg)](https://gitlab.com/adynemo/maintenance-bundle/-/commits/master)

</div>

Overview
========

This bundle allows you to place your website in maintenance mode by calling two commands in your console. A page with status code 503 appears to users, 
it is possible to authorize certain ips addresses stored in your configuration.

Several choices of maintenance mode are possible: a simple test of an existing file, or memcache, or in a database.

Original LexikMaintenanceBundle is no longer maintained, this fork has the ambition to support new Symfony and PHP versions.

| Support | Version     |
| ------- | ------------|
| Symfony | ^4.0 / ^5.0 |
| PHP     | ^7.2 / ^8.0 |

---------------------

Documentation
=============

For installation and how to use the bundle refer to [Resources/doc/index.md](https://gitlab.com/adynemo/maintenance-bundle/-/tree/master/Resources/doc)

Contributing
============

If you have any issue, please submit it on:
- [GitLab](https://gitlab.com/adynemo/maintenance-bundle/-/issues/new)
- [GitHub](https://github.com/adynemo/MaintenanceBundle/issues/new)
