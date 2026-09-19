# Changelog

All notable changes to `package` will be documented in this file.

## v0.0.1 - 2026-09-19

### What's Changed

* first setup by @salahhusa9 in https://github.com/fastchartdev/package/pull/1
* Add migration and model files for events, meters, and summaries; introduce enums for aggregation, event record status, and period types by @salahhusa9 in https://github.com/fastchartdev/package/pull/2
* Add queue configuration, event and meter migrations, and RecordEventJob implementation by @salahhusa9 in https://github.com/fastchartdev/package/pull/3
* Implement recordEvent method to handle event creation and dispatching by @salahhusa9 in https://github.com/fastchartdev/package/pull/4
* Refactor database connection configuration for Event, EventRecord, Meter, and MeterSummary models by @salahhusa9 in https://github.com/fastchartdev/package/pull/5
* Add query method and custom exceptions for event handling and data retrieval by @salahhusa9 in https://github.com/fastchartdev/package/pull/6
* Fix database connection configuration in migration files for events, event_records, and meters by @salahhusa9 in https://github.com/fastchartdev/package/pull/7
* Refactor RecordEventJob to create meters when none are found for an event by @salahhusa9 in https://github.com/fastchartdev/package/pull/8
* Limit failure reason length to 255 characters in RecordEventJob and add event_id to meter creation by @salahhusa9 in https://github.com/fastchartdev/package/pull/9
* Fix database connection configuration in RecordEventJob transactions by @salahhusa9 in https://github.com/fastchartdev/package/pull/10
* Update README.md for FastChart: enhance documentation, add configuration details, and improve event recording examples by @salahhusa9 in https://github.com/fastchartdev/package/pull/11

### New Contributors

* @salahhusa9 made their first contribution in https://github.com/fastchartdev/package/pull/1

**Full Changelog**: https://github.com/fastchartdev/package/commits/v0.0.1
