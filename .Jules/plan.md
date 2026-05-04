1. **Extract artwork from the portfolio endpoint.**
   - Add `getPortfolio` to `src/lib/wp.ts` to fetch from `/wp/v2/portfolio?_embed`.
   - Update `PortfolioPage` in `src/app/portfolio/page.tsx` to fetch from the portfolio endpoint instead of (or in addition to) the media endpoint.
   - Ensure the data mapping correctly extracts the featured media URLs.
2. **Verify the new data integration.**
   - Confirm that the 3D gallery and traditional view correctly display the portfolio items.
   - Run `pnpm lint` and `pnpm build` to ensure no regressions.
3. **Complete pre commit steps.**
   - Complete pre commit steps to make sure proper testing, verifications, reviews and reflections are done.
4. **Submit the change.**
   - Once all tests pass, I will submit the change with a descriptive commit message.
