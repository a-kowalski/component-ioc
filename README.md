# Change Log
This is the Maleficarum IOC Container implementation. 

## [3.0.1] - 2018-10-12
### Fixed
- Class name will now be properly passed to the builder function.

## [3.0.0] - 2018-09-04
> CAUTION:
> this version completely breaks backwards compatibility with 2.x. DO NOT just replace older version with this one.
### Changed
- Changed dependency label to share: This means that registerDependency() and getDependency() methods have been renamed to registerShare() and retrieveShare() respectively.
- Reversed builder lookup order. With this new version the get() method will go from generic to specific and pass the result of each builder to the next one for refinement.
- Removed default builder file. If you want to have builders automatically imported you need to register each namespace. (This does not apply to builders added via initializers or manual code execution)
- Removed builder appends. They were never as useful as we hoped.
- Updated requirements to PHP 7.2

## [2.2.0] - 2017-08-10
### Added
- Added support for builder appends.

## [2.1.0] - 2017-03-23
### Added
- Added a way to access registered dependencies from outside of builder functions.

## [2.0.1] - 2017-02-15
### Added
- Add tests.

## [2.0.0] - 2017-01-23
### Changed
- Add return and argument types declaration

## [1.1.0] - 2016-10-03
### Changed
- Made PHP7 compatible.
- Refactored how default builders are defined. They no longer are part of the general namespace list but defined as a separate option.
- Refactored unit tests to reflect new functionality and have a more readable structure.

## [1.0.0] - 2016-09-23
### Added
- This was an initial release based on the code written by me and added to the repo by a-kowalski (Thanks !!!)
