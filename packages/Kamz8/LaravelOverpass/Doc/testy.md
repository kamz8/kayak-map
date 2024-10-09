# Test Scenarios for `Laravel Overpass`

This document provides an overview of the various test scenarios for the Laravel Overpass package, covering the functionalities of `BoundingBoxHelper`, `RouteHelper`, and `Overpass` classes. The tests were migrated from PHPUnit to Pest and are designed to ensure the reliability and accuracy of the implemented features.

## Table of Contents

- [BoundingBoxHelper Test Scenarios](#boundingboxhelper-test-scenarios)
- [RouteHelper Test Scenarios](#routehelper-test-scenarios)
- [Overpass Test Scenarios](#overpass-test-scenarios)

---

## BoundingBoxHelper Test Scenarios

### 1. Calculates Bounding Box Correctly
- **Description**: Verify that the `BoundingBoxHelper::calculateBoundingBox()` method calculates the bounding box correctly when given valid start and end coordinates.
- **Input**: Start coordinates (`51.5, -0.1`) and end coordinates (`51.6, 0.1`).
- **Expected Output**: The method should return an array with four values: `[51.5, -0.1, 51.6, 0.1]`.
- **Validation**:
    - Assert the result is an array.
    - Assert the array contains exactly four elements.
    - Assert that each element corresponds to the input coordinates.

### 2. Throws an Exception for Invalid Coordinates
- **Description**: Verify that the method throws an exception when given invalid latitude or longitude values.
- **Input**: Start coordinates (`200, -0.1`) and end coordinates (`51.6, 0.1`). The start latitude is invalid as it exceeds the maximum allowed range.
- **Expected Output**: The method should throw an `InvalidArgumentException`.
- **Validation**:
    - Assert that an exception is thrown with the correct type.

### 3. Handles Negative Longitude Correctly
- **Description**: Verify that the method correctly calculates the bounding box when both the start and end longitudes are negative.
- **Input**: Start coordinates (`51.5, -0.5`) and end coordinates (`51.6, -0.3`).
- **Expected Output**: The method should return an array with four values: `[51.5, -0.5, 51.6, -0.3]`.
- **Validation**:
    - Assert the result is an array.
    - Assert the array contains exactly four elements.
    - Assert that each element matches the given input.

---

## RouteHelper Test Scenarios

### 1. Generates Route Correctly Between Two Valid Points
- **Description**: Verify that `RouteHelper::generateRoute()` correctly generates a route between two valid points.
- **Input**: Start coordinates (`51.5, -0.1`) and end coordinates (`51.6, 0.1`).
- **Expected Output**: The generated route should be an array of strings, where each element represents a latitude/longitude pair.
- **Validation**:
    - Assert the result is an array.
    - Assert the array is not empty.
    - Assert that the first element matches the start point (`51.5,-0.1`).
    - Assert that the last element matches the end point (`51.6,0.1`).

### 2. Throws an Exception When Generating Route with Invalid Coordinates
- **Description**: Verify that the method throws an exception when given invalid coordinates.
- **Input**: Start coordinates (`200, -0.1`) and end coordinates (`51.6, 0.1`). The start latitude is invalid.
- **Expected Output**: The method should throw an `InvalidArgumentException`.
- **Validation**:
    - Assert that an exception is thrown with the correct type.

### 3. Generates Empty Route for Identical Start and End Points
- **Description**: Verify that the method returns an empty or minimal route when the start and end coordinates are the same.
- **Input**: Start coordinates (`51.5, -0.1`) and end coordinates (`51.5, -0.1`).
- **Expected Output**: The method should return an array containing only one element representing the start/end point.
- **Validation**:
    - Assert the result is an array.
    - Assert the array contains exactly one element.
    - Assert that the element matches the start/end point (`51.5,-0.1`).

---

## Overpass Test Scenarios

### 1. Executes a Simple Query and Returns Valid Response
- **Description**: Verify that the `Overpass::query()` method executes a simple Overpass query correctly and returns a valid response.
- **Input**: A query that searches for nodes with the amenity type `cafe` within a bounding box (`51.5, -0.1, 51.6, 0.1`) and limits the results to 5 nodes.
- **Expected Output**: The query should return an array containing 5 results.
- **Validation**:
    - Assert that the response is not `null`.
    - Assert that the response is an array.
    - Assert that the response contains exactly 5 elements.

### 2. Throws an Exception When Query is Malformed
- **Description**: Verify that the method throws an exception when a malformed query is executed.
- **Input**: A query with an invalid key-value pair (`invalidKey`, `invalidValue`).
- **Expected Output**: The method should throw an `Exception`.
- **Validation**:
    - Assert that an exception is thrown with the correct type.

### 3. Handles Empty Result Set Correctly
- **Description**: Verify that the method handles an empty result set correctly when no nodes match the query.
- **Input**: A query searching for nodes with an amenity type that does not exist (`nonexistent`) within a bounding box (`51.5, -0.1, 51.6, 0.1`) and limits the results to 5.
- **Expected Output**: The method should return an empty array.
- **Validation**:
    - Assert that the response is an array.
    - Assert that the response is empty.

