# VBCompetitions/competitions-php

A PHP library for interacting with VBCompetition files, including updating the results, parsing the state of the competition and generating style-able HTML representations of the competition

## Notes
- On load, any value with a default defined in the JSON schema will take that default value
- JSON data does not round-trip, i.e. it is not guaranteed to be saved byte-for-byte the same as the loaded JSON:
  - Any empty value with a default value defined in the schema is saved with that default value explicitly stated
  - Any arrays without content will be saved as an empty array
  - Redundant fields may be dropped, e.g. the "league" config in a knockout group, or the "sets" config when the matches are "continuous"
  - Data is saved in the order defined in the schema
