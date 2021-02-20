## [1.1.2](https://github.com/adhocore/php-cron-expr/releases/tag/1.1.2) (2021-02-20)

### Features
- Add expression normalizer (Jitendra Adhikari) [_afb57c4_](https://github.com/adhocore/php-cron-expr/commit/afb57c4)

### Internal Refactors
- Use Normalizer instead (Jitendra Adhikari) [_42700ab_](https://github.com/adhocore/php-cron-expr/commit/42700ab)

### Documentations
- Tags as constants (Jitendra Adhikari) [_1382d7d_](https://github.com/adhocore/php-cron-expr/commit/1382d7d)


## [1.1.1](https://github.com/adhocore/php-cron-expr/releases/tag/1.1.1) (2020-01-09)

### Miscellaneous
- **Validator**: Cleanup redundancy (Jitendra Adhikari) [_00983d4_](https://github.com/adhocore/php-cron-expr/commit/00983d4)
- **Travis**: Script (Jitendra Adhikari) [_fec5332_](https://github.com/adhocore/php-cron-expr/commit/fec5332)
- **Composer**: Tweak script.test (Jitendra Adhikari) [_0a5b4fb_](https://github.com/adhocore/php-cron-expr/commit/0a5b4fb)

### Documentations
- Update benchmark (Jitendra Adhikari) [_fe9dffd_](https://github.com/adhocore/php-cron-expr/commit/fe9dffd)


## [1.1.0](https://github.com/adhocore/php-cron-expr/releases/tag/1.1.0) (2019-12-27)

### Features
- Add ref time class (Jitendra Adhikari) [_2ad504b_](https://github.com/adhocore/php-cron-expr/commit/2ad504b)

### Bug Fixes
- **Expr**: Replace literals case insensitive (Jitendra Adhikari) [_a5c179f_](https://github.com/adhocore/php-cron-expr/commit/a5c179f)

### Internal Refactors
- **Validator**: Use reference time class (Jitendra Adhikari) [_05f139d_](https://github.com/adhocore/php-cron-expr/commit/05f139d)
- **Checker**: Use reference time class (Jitendra Adhikari) [_7b4138f_](https://github.com/adhocore/php-cron-expr/commit/7b4138f)
- **Expr**: Use reference time class, cleanup process() (Jitendra Adhikari) [_1ce873d_](https://github.com/adhocore/php-cron-expr/commit/1ce873d)

### Miscellaneous
- **Reftime**: Add method annot for magic calls (Jitendra Adhikari) [_96b78d1_](https://github.com/adhocore/php-cron-expr/commit/96b78d1)
- Ignore coverage xml (Jitendra Adhikari) [_2a9505a_](https://github.com/adhocore/php-cron-expr/commit/2a9505a)
- **Composer**: Bump deps version, fix test:cov (Jitendra Adhikari) [_14e9117_](https://github.com/adhocore/php-cron-expr/commit/14e9117)

### Documentations
- About cron (Jitendra Adhikari) [_a3760f8_](https://github.com/adhocore/php-cron-expr/commit/a3760f8)


## [1.0.0](https://github.com/adhocore/php-cron-expr/releases/tag/1.0.0) (2019-12-22)

### Internal Refactors
- Strict php7 typehints (Jitendra Adhikari) [_e0967be_](https://github.com/adhocore/php-cron-expr/commit/e0967be)

### Miscellaneous
- Add composer script (Jitendra Adhikari) [_594e7e1_](https://github.com/adhocore/php-cron-expr/commit/594e7e1)

### Documentations
- Update for v1.0 (Jitendra Adhikari) [_7224037_](https://github.com/adhocore/php-cron-expr/commit/7224037)

### Builds
- **Travis**: Php7 only (Jitendra Adhikari) [_b0170db_](https://github.com/adhocore/php-cron-expr/commit/b0170db)


## [v0.1.0](https://github.com/adhocore/php-cron-expr/releases/tag/v0.1.0) (2019-09-22)

### Documentations
- About stability (Jitendra Adhikari) [_1672edc_](https://github.com/adhocore/php-cron-expr/commit/1672edc)
- Add php support info (Jitendra Adhikari) [_9d21717_](https://github.com/adhocore/php-cron-expr/commit/9d21717)


## [v0.0.7](https://github.com/adhocore/php-cron-expr/releases/tag/v0.0.7) (2019-08-12)

### Internal Refactors
- **Expr**: Normalize expr, use regex split instead (Jitendra Adhikari) [_74f8dfc_](https://github.com/adhocore/php-cron-expr/commit/74f8dfc)


## [v0.0.6] 2018-08-16 00:08:45 UTC

- [d933099](https://github.com/adhocore/php-cron-expr/commit/d933099) fix(expr): static ::instance()
- [48eef4a](https://github.com/adhocore/php-cron-expr/commit/48eef4a) docs: bulk checks/filters
- [8c23489](https://github.com/adhocore/php-cron-expr/commit/8c23489) test(expr): ...
- [aab941e](https://github.com/adhocore/php-cron-expr/commit/aab941e) refactor(expr): simplify filter(), also cache undue case
- [cd3cd33](https://github.com/adhocore/php-cron-expr/commit/cd3cd33) test(expr): filter(), getDues()
- [7ec07c8](https://github.com/adhocore/php-cron-expr/commit/7ec07c8) feat(checker): make validator injectable
- [0d6d85a](https://github.com/adhocore/php-cron-expr/commit/0d6d85a) feat(expr): add construct with injection/initiliazation
- [b8aa4ce](https://github.com/adhocore/php-cron-expr/commit/b8aa4ce) feat(expr): add ::instance() api
- [cee9bf7](https://github.com/adhocore/php-cron-expr/commit/cee9bf7) feat(expr): add filter(), ::getDues() and normalizeExpr()
- [d73a078](https://github.com/adhocore/php-cron-expr/commit/d73a078) refactor(docblocks): document public apis/methods
- [ee2cc96](https://github.com/adhocore/php-cron-expr/commit/ee2cc96) refactor: rename array of time parts to times

## [v0.0.5] 2018-08-15 10:08:16 UTC

- [6fccf54](https://github.com/adhocore/php-cron-expr/commit/6fccf54) test(validator): instep changes
- [93d2f7f](https://github.com/adhocore/php-cron-expr/commit/93d2f7f) fix(validator): inStep when start+step>end | closes #12
- [895eade](https://github.com/adhocore/php-cron-expr/commit/895eade) docs: update readme
- [bc65912](https://github.com/adhocore/php-cron-expr/commit/bc65912) docs: fix badge
