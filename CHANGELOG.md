# Changelog

## v1.1.10 (2026-10-28)

### Fixed bugs
* **The model parser's items declare their properties.** `ModelItem`, `PropertyItem` and
  `ModelParser` itself set properties they only named in a `@property` line, and PHP 8.2 and later
  reports each such property as deprecated each time it is set, and CodeIgniter logs every one
  with its stack trace. Every property is declared now, with the default it had, so nothing is
  reported and nothing else changes: the schemas and the TypeScript and Xamarin exports are as they
  were. An interface's model no longer reads the undefined `isResource` in the TypeScript export
  either, which was a warning of its own.
