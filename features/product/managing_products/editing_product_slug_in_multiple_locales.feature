@managing_products
Feature: Editing product's slug in multiple locales
    In order to manage access path to product page in many languages
    As an Administrator
    I want to be able to edit product's slug in multiple locales

    Background:
        Given the store operates on a single channel in "United States"
        And the store is available in "English (United States)"
        And the store is also available in "Polish (Poland)"
        And I am logged in as an administrator

    @ui @no-api
    Scenario: Creating a product with custom slugs
        When I want to create a new simple product
        And I specify its code as "PUG_PUGGINTON_PLUSHIE"
        And I set its price to "$100.00" for "United States" channel
        And I set its price to "$100.00" for "United States" channel
        And I name it "Pug Pugginton Plushie" in "English (United States)"
        And I set its slug to "sir-pugginton" in "English (United States)"
        And I name it "Pluszak Mops Mopsiński" in "Polish (Poland)"
        And I set its slug to "pan-mopsinski" in "Polish (Poland)"
        And I add it
        Then the slug of the "Pug Pugginton Plushie" product should be "sir-pugginton" in the "English (United States)" locale
        And the slug of the "Pug Pugginton Plushie" product should be "pan-mopsinski" in the "Polish (Poland)" locale

    @ui @mink:chromedriver @no-api
    Scenario: Creating a product with autogenerated slugs
        When I want to create a new simple product
        And I specify its code as "PUG_PUGGINTON_PLUSHIE"
        And I set its price to "$100.00" for "United States" channel
        And I name it "Pug Pugginton Plushie" in "English (United States)"
        And I name it "Pluszak Mops Mopsiński" in "Polish (Poland)"
        And I add it
        Then the slug of the "Pug Pugginton Plushie" product should be "pug-pugginton-plushie" in the "English (United States)" locale
        And the slug of the "Pug Pugginton Plushie" product should be "pluszak-mops-mopsinski" in the "Polish (Poland)" locale

    @ui
    Scenario: Seeing disabled slug fields when editing a product
        Given the store has a product named "Pug Pugginton Plushie" in "English (United States)" locale and "Pluszak Mops Mopsiński" in "Polish (Poland)" locale
        When I want to modify this product
        Then the slug field in "English (United States)" should not be editable
        And the slug field in "Polish (Poland)" also should not be editable

    @ui @mink:chromedriver
    Scenario: Slugs don't get updated while changing product's names
        Given the store has a product named "Pug Pugginton Plushie" in "English (United States)" locale and "Pluszak Mops Mopsiński" in "Polish (Poland)" locale
        When I want to modify this product
        And I rename it to "Pug Pugston the Third Plushie" in "English (United States)"
        And I rename it to "Pluszak Mops Mopsak Trzeci" in "Polish (Poland)"
        And I save my changes
        Then this product should still have slug "pug-pugginton-plushie" in "English (United States)"
        And this product should still have slug "pluszak-mops-mopsinski" in "Polish (Poland)"

    @ui @mink:chromedriver
    Scenario: Enabling automatic slugs update on product's names change
        Given the store has a product named "Pug Pugginton Plushie" in "English (United States)" locale and "Pluszak Mops Mopsiński" in "Polish (Poland)" locale
        When I want to modify this product
        And I enable slug modification in "English (United States)"
        And I rename it to "Pug Pugston the Third Plushie" in "English (United States)"
        And I enable slug modification in "Polish (Poland)"
        And I rename it to "Pluszak Mops Mopsak Trzeci" in "Polish (Poland)"
        And I save my changes
        Then this product should have slug "pug-pugston-the-third-plushie" in "English (United States)"
        And this product should have slug "pluszak-mops-mopsak-trzeci" in "Polish (Poland)"

    @ui @mink:chromedriver
    Scenario: Manually modifying slugs on product's names change
        Given the store has a product named "Pug Pugginton Plushie" in "English (United States)" locale and "Pluszak Mops Mopsiński" in "Polish (Poland)" locale
        When I want to modify this product
        And I enable slug modification in "English (United States)"
        And I rename it to "Pug Pugston the Third Plushie" in "English (United States)"
        And I set its slug to "sir-pugston-the-third" in "English (United States)"
        And I enable slug modification in "Polish (Poland)"
        And I rename it to "Pluszak Mops Mopsak Trzeci" in "Polish (Poland)"
        And I set its slug to "pan-mopsak-trzeci" in "Polish (Poland)"
        And I save my changes
        Then this product should have slug "sir-pugston-the-third" in "English (United States)"
        And this product should have slug "pan-mopsak-trzeci" in "Polish (Poland)"
