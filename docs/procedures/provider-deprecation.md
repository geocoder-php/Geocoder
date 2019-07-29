# Deprecating providers

A provider is considered shut down when the APIs no longer respond to queries with a valid geocoded response.
If the provider has alternatives that are a drop in replacement, or offer self hosting it should not be deprecated.

## Steps

1) Update Provider class, adding `@deprecated` annotations to the class and constructor method.
2) Remove the provider from the main repository README
3) Update the provider README to indicate that the package is abandoned, and where possible include alternatives.
4) Publish new package version (containing the `@deprecated` annotations) to Packagist
5) Mark Packagist package as abandoned