#!/usr/bin/env python3
"""
Main orchestrator for the AI Editorial Generation system.

This script coordinates the entire editorial generation process
including problem processing, AC solution management, editorial
generation, and website uploading.
"""

import sys
from pathlib import Path
from typing import Dict, Any, Optional, List

# Import with fallback for relative imports
try:
    from .ai_editorial_generator import (  # type: ignore
        EditorialGeneratorConfig,
        StatsTracker,
        OmegaUpAPIClient,
        setup_logging
    )
except ImportError:
    from ai_editorial_generator import (
        EditorialGeneratorConfig,
        StatsTracker,
        OmegaUpAPIClient,
        setup_logging
    )

try:
    from .solution_handler import SolutionHandler  # type: ignore
except ImportError:
    from solution_handler import SolutionHandler

try:
    from .editorial_generator import EditorialGenerator  # type: ignore
except ImportError:
    from editorial_generator import EditorialGenerator

try:
    from .website_uploader import WebsiteUploader  # type: ignore
except ImportError:
    from website_uploader import WebsiteUploader

logger = setup_logging()


class EditorialGenerationOrchestrator:
    """Main orchestrator for editorial generation process."""

    def __init__(self) -> None:
        """Initialize the orchestrator with all required components."""
        self.config = EditorialGeneratorConfig()
        self.stats = StatsTracker()
        self.api_client = OmegaUpAPIClient(self.config)
        self.solution_handler = SolutionHandler(
            self.config, self.api_client.session
        )
        self.editorial_generator = EditorialGenerator(self.config)
        self.website_uploader = WebsiteUploader(
            self.config, self.api_client.session
        )

        logger.info(
            "AI-Based Multi-Language Editorial Generator initialized "
            "successfully"
        )

    def process_problem(self, problem_alias: str) -> Dict[str, Any]:
        """Process single problem with AC-based multi-language editorial."""
        result: Dict[str, Any] = {
            'problem_alias': problem_alias,
            'has_existing_editorial': False,
            'has_admin_access': False,
            'used_existing_ac': False,
            'generated_ac_solution': False,
            'editorials_generated': {},
            'website_updates': {},
            'success': False,
            'error': None
        }

        logger.info("=" * 60)
        logger.info("PROCESSING PROBLEM: %s", problem_alias)
        logger.info("=" * 60)

        # Step 1: Check if editorial already exists
        if self.api_client.check_existing_editorial(problem_alias):
            result['has_existing_editorial'] = True
            result['error'] = "Editorial already exists - skipped"
            self.stats.increment('problems_with_existing_editorial')
            self.stats.append_to_list(
                'existing_editorial_problems', problem_alias
            )
            return result

        self.stats.increment('problems_needing_editorial')

        # Step 2: Get problem details
        problem_details = self.api_client.get_problem_details(problem_alias)
        if not problem_details:
            result['error'] = "Failed to fetch problem details"
            self.stats.increment('editorials_failed')
            self.stats.append_to_list(
                'failed_editorial_problems', problem_alias
            )
            return result

        problem_title = problem_details.get('title', 'Unknown')
        logger.info("Problem title: %s", problem_title)

        # Step 3: Handle AC solution (existing or generated)
        logger.info("[%s] Managing AC solution...", problem_alias)
        ac_solution, language = self._get_or_generate_ac_solution(
            problem_alias, problem_details, result
        )

        if not ac_solution:
            return self._handle_ac_solution_failure(result, problem_alias)

        # Step 4: Generate and upload editorials
        problem_context = {
            'alias': problem_alias,
            'details': problem_details,
            'ac_solution': ac_solution,
            'language': language
        }
        return self._generate_and_upload_editorials(result, problem_context)

    def _handle_ac_solution_failure(
        self, result: Dict[str, Any], problem_alias: str
    ) -> Dict[str, Any]:
        """Handle case where AC solution generation failed."""
        result['error'] = (
            "Failed to generate AC solution after 2 attempts - "
            "skipping editorial"
        )
        logger.error("[%s] *** EDITORIAL GENERATION SKIPPED ***",
                     problem_alias)
        logger.error("[%s] No AC solution after 2 attempts", problem_alias)
        logger.error("[%s] Needs working solution", problem_alias)
        self.stats.increment('editorials_failed')
        self.stats.append_to_list(
            'failed_editorial_problems', problem_alias
        )
        return result

    def _generate_and_upload_editorials(
        self,
        result: Dict[str, Any],
        problem_context: Dict[str, Any]
    ) -> Dict[str, Any]:
        """Generate multi-language editorials and upload them."""
        problem_alias = problem_context['alias']
        problem_details = problem_context['details']
        ac_solution = problem_context['ac_solution']
        language = problem_context['language']

        # Step 5: Generate multi-language editorials using AC solution
        logger.info("\n[%s] Generating multi-lang editorials...",
                    problem_alias)
        editorials = self._generate_editorials(
            problem_details, ac_solution, language
        )

        if not (editorials and len(editorials) == 3):
            return self._handle_editorial_generation_failure(
                result, problem_alias
            )

        # Mark all languages as successfully generated
        for lang_code in editorials.keys():
            result['editorials_generated'][lang_code] = True

        # Log all generated editorials with content
        self._log_generated_editorials(editorials)
        logger.info("Generated %d/%d", len(editorials),
                    len(self.config.target_languages))

        # Step 6: Upload editorials and determine success
        return self._upload_and_finalize_result(
            result, problem_alias, problem_details, editorials
        )

    def _handle_editorial_generation_failure(
        self, result: Dict[str, Any], problem_alias: str
    ) -> Dict[str, Any]:
        """Handle case where editorial generation failed."""
        # Mark all languages as failed
        for lang_code in self.config.target_languages.keys():
            result['editorials_generated'][lang_code] = False

        result['error'] = "Failed to generate multi-language editorials"
        self.stats.increment('editorials_failed')
        self.stats.append_to_list(
            'failed_editorial_problems', problem_alias
        )
        return result

    def _log_generated_editorials(self, editorials: Dict[str, str]) -> None:
        """Log all generated editorials with content."""
        for lang_code, editorial in editorials.items():
            lang_name = self.config.target_languages[lang_code]
            logger.info("=" * 80)
            logger.info("*** GENERATED %s EDITORIAL CONTENT ***",
                        lang_name.upper())
            logger.info("=" * 80)
            for i, line in enumerate(editorial.split('\n'), 1):
                logger.info("%3d | %s", i, line)
            logger.info("=" * 80)

    def _upload_and_finalize_result(
        self,
        result: Dict[str, Any],
        problem_alias: str,
        problem_details: Dict[str, Any],
        editorials: Dict[str, str]
    ) -> Dict[str, Any]:
        """Upload editorials and finalize result."""
        # Step 6: Upload separate editorials for each language using lang param
        logger.info(
            "\nUploading separate editorials for each language to website..."
        )

        successful_uploads = 0
        upload_results = {}
        for lang_code, editorial_content in editorials.items():
            lang_name = self.config.target_languages[lang_code]
            logger.info("\nUploading %s editorial...", lang_name)

            upload_success = (
                self.website_uploader.upload_editorial_for_language(
                    problem_alias, editorial_content, lang_code
                )
            )

            if upload_success:
                successful_uploads += 1
                upload_results[lang_code] = True
                logger.info("✓ %s editorial uploaded successfully", lang_name)
            else:
                upload_results[lang_code] = False
                logger.error("✗ %s editorial upload failed", lang_name)

        # Determine overall success
        if successful_uploads > 0:
            success_context = {
                'alias': problem_alias,
                'details': problem_details,
                'editorials': editorials,
                'successful_uploads': successful_uploads,
                'upload_results': upload_results
            }
            self._handle_upload_success(result, success_context)
        else:
            self._handle_upload_failure(result, problem_alias)

        result['website_updates'] = upload_results
        return result

    def _handle_upload_success(
        self,
        result: Dict[str, Any],
        success_context: Dict[str, Any]
    ) -> None:
        """Handle successful upload case."""
        problem_alias = success_context['alias']
        problem_details = success_context['details']
        editorials = success_context['editorials']
        successful_uploads = success_context['successful_uploads']
        upload_results = success_context['upload_results']

        result['success'] = True
        self.stats.increment('editorials_generated_successfully')
        self.stats.append_to_list(
            'generated_editorial_problems', problem_alias
        )
        self.stats.increment('website_updates_successful')

        logger.info("")
        logger.info("=" * 80)
        logger.info("[%s] MULTI-LANGUAGE EDITORIAL SUCCESS!",
                    problem_alias)
        logger.info("=" * 80)
        logger.info("Problem: %s", problem_details.get('title', 'Unknown'))
        logger.info("Editorials: %d/%d", len(editorials),
                    len(self.config.target_languages))
        logger.info("Uploads: %d/%d", successful_uploads, len(editorials))

        # Show which languages were successful
        for lang_code, success in upload_results.items():
            lang_name = self.config.target_languages[lang_code]
            status = "✓" if success else "✗"
            logger.info("  %s %s (%s)", status, lang_name, lang_code)

        logger.info("=" * 80)

    def _handle_upload_failure(
        self, result: Dict[str, Any], problem_alias: str
    ) -> None:
        """Handle upload failure case."""
        result['error'] = "All website uploads failed"
        self.stats.increment('editorials_failed')
        self.stats.append_to_list(
            'failed_editorial_problems', problem_alias
        )
        self.stats.increment('website_updates_failed')

    def _get_or_generate_ac_solution(
        self,
        problem_alias: str,
        problem_details: Dict[str, Any],
        result: Dict[str, Any]
    ) -> tuple[Optional[str], str]:
        """Get existing AC solution or generate one."""
        language = "cpp17-gcc"

        # Check for existing AC solution if admin access available
        if self.api_client.check_admin_access(problem_alias):
            result['has_admin_access'] = True
            self.stats.increment('problems_with_admin_access')
            self.stats.append_to_list(
                'admin_access_problem_names', problem_alias
            )
            ac_run = self.solution_handler.get_first_ac_run(problem_alias)
            if ac_run:
                run_guid = ac_run.get('guid', '')
                if run_guid:
                    ac_solution = self.solution_handler.get_run_source(
                        run_guid
                    )
                    if ac_solution:
                        result['used_existing_ac'] = True
                        language = ac_run.get('language', 'cpp17-gcc')
                        self.stats.increment('problems_with_existing_ac')
                        self.stats.append_to_list(
                            'existing_ac_problem_names', problem_alias
                        )
                        logger.info("[%s] *** USING EXISTING AC ***",
                                    problem_alias)
                        logger.info("[%s] Language: %s", problem_alias,
                                    language)
                        self._log_ac_solution(
                            ac_solution, "AC SOLUTION FOR EDITORIAL"
                        )
                        return ac_solution, language

        # Generate new AC solution if none found
        logger.info("[%s] No AC solution - generating...", problem_alias)
        ac_solution = self.solution_handler.generate_and_verify_solution(
            problem_details, problem_alias, language
        )

        if ac_solution:
            result['generated_ac_solution'] = True
            self.stats.increment('problems_ac_generated')
            self.stats.append_to_list(
                'verified_ac_problem_names', problem_alias
            )
            self._log_ac_solution(
                ac_solution, "GENERATED AC SOLUTION FOR EDITORIAL"
            )
            return ac_solution, language

        self.stats.increment('problems_ac_verification_failed')
        self.stats.append_to_list(
            'ac_verification_failed_problems', problem_alias
        )

        return None, language

    def _generate_editorials(
        self,
        problem_details: Dict[str, Any],
        ac_solution: str,
        language: str
    ) -> Optional[Dict[str, str]]:
        """Generate multi-language editorials."""
        editorials = self.editorial_generator.generate_multilanguage_editorial(
            problem_details, ac_solution, language
        )
        if editorials and len(editorials) == 3:
            logger.info("Generated %d language editorials", len(editorials))
            return editorials  # type: ignore
        return None

    def _upload_editorials(
        self, problem_alias: str, editorials: Dict[str, str]
    ) -> Dict[str, bool]:
        """Upload editorials to website."""
        uploader = WebsiteUploader(self.config, self.api_client.session)
        return uploader.upload_editorials_for_all_languages(  # type: ignore
            problem_alias, editorials
        )

    def _log_editorials(
        self, editorials: Dict[str, str]
    ) -> None:
        """Log generated editorial content."""
        for lang_code, editorial in editorials.items():
            lang_name = self.config.target_languages[lang_code]
            logger.info("=" * 80)
            logger.info("*** GENERATED %s EDITORIAL CONTENT ***",
                        lang_name.upper())
            logger.info("=" * 80)
            for i, line in enumerate(editorial.split('\n'), 1):
                logger.info("%3d | %s", i, line)
            logger.info("=" * 80)

    def _log_success_summary(
        self,
        problem_alias: str,
        problem_details: Dict[str, Any],
        editorials: Dict[str, str],
        upload_results: Dict[str, bool]
    ) -> None:
        """Log success summary."""
        problem_title = problem_details.get('title', 'Unknown')
        successful_uploads = sum(upload_results.values())

        logger.info("")
        logger.info("=" * 80)
        logger.info("[%s] MULTI-LANGUAGE EDITORIAL SUCCESS!", problem_alias)
        logger.info("=" * 80)
        logger.info("Problem: %s", problem_title)
        logger.info("Editorials: %d/%d", len(editorials),
                    len(self.config.target_languages))
        logger.info("Uploads: %d/%d", successful_uploads, len(editorials))

        for lang_code, success in upload_results.items():
            lang_name = self.config.target_languages[lang_code]
            status = "✓" if success else "✗"
            logger.info("  %s %s (%s)", status, lang_name, lang_code)
        logger.info("=" * 80)

    def _log_ac_solution(self, code: str, solution_type: str) -> None:
        """Log AC solution code."""
        logger.info("=" * 60)
        logger.info("*** %s ***", solution_type)
        logger.info("=" * 60)
        for i, line in enumerate(code.split('\n'), 1):
            logger.info("%3d | %s", i, line)
        logger.info("=" * 60)

    def load_problems_from_file(self, filename: str) -> List[str]:
        """Load problem aliases from text file."""
        try:
            filepath = Path(filename)
            if not filepath.exists():
                logger.error("File not found: %s", filename)
                return []

            problems = []
            with open(filepath, 'r', encoding='utf-8') as f:
                for line in f:
                    line = line.strip()
                    if line and not line.startswith('#'):
                        problems.append(line)

            logger.info("Loaded %d problems from %s", len(problems), filename)
            return problems

        except (IOError, OSError) as e:
            logger.error("Error reading file %s: %s", filename, str(e))
            return []

    def run_editorial_generation(self, problems: List[str]) -> None:
        """Run editorial generation for all problems."""
        if not problems:
            logger.error("No problems to process")
            return

        self.stats.set_value('total_problems', len(problems))

        logger.info("=" * 80)
        logger.info("STARTING AC-BASED MULTI-LANGUAGE EDITORIAL GENERATION")
        logger.info("=" * 80)
        logger.info("Total problems: %d", len(problems))
        logger.info("Target languages: %s",
                    ', '.join(self.config.target_languages.values()))
        logger.info("=" * 80)

        for i, problem_alias in enumerate(problems, 1):
            logger.info("\n\n%s", '=' * 80)
            logger.info("PROGRESS: %d/%d - Processing: %s", i, len(problems),
                        problem_alias)
            logger.info("=" * 80)

            try:
                result = self.process_problem(problem_alias)
                self.stats.append_to_list('problem_results', result)

                # Track statistics exactly like AiEG1.py
                if result.get('has_existing_editorial'):
                    logger.info(
                        "SKIPPED: %s - Editorial already exists",
                        problem_alias)

                if result.get('error'):
                    if 'Karel' in result.get('error', ''):
                        self.stats.increment('karel_skipped')
                        self.stats.append_to_list(
                            'karel_problem_names', problem_alias
                        )
                        logger.info(
                            "KAREL SKIP: %s - Karel-only problem",
                            problem_alias
                        )
                    elif ('Failed to generate AC solution after 2 attempts'
                          in result.get('error', '')):
                        logger.error(
                            "AC VERIFICATION FAILED: %s - "
                            "No verified solution after 2 attempts",
                            problem_alias)
                    elif 'Editorial already exists' not in result.get(
                            'error', ''):
                        logger.error(
                            "FAILED: %s - %s", problem_alias,
                            result.get('error', ''))

                # Track successful editorial generation
                if result.get('success'):
                    logger.info(
                        "SUCCESS: %s - Multi-lang editorials "
                        "generated and uploaded successfully", problem_alias)

            except (ValueError, RuntimeError) as e:
                logger.error("Unexpected error processing %s: %s",
                             problem_alias, str(e))
                self.stats.increment('editorials_failed')
                self.stats.append_to_list(
                    'failed_editorial_problems', problem_alias
                )

        # Print final statistics
        self._print_final_statistics()

    def _print_final_statistics(self) -> None:
        """Print comprehensive final statistics."""
        logger.info("\n\n")
        logger.info("=" * 80)
        logger.info(
            "FINAL STATISTICS - AC-BASED MULTI-LANGUAGE EDITORIAL GENERATION")
        logger.info("=" * 80)

        # Get main statistics
        stats_data = self._gather_statistics()

        # Print main sections
        self._print_processing_summary(stats_data)
        self._print_ac_solution_statistics(stats_data)
        self._print_success_rate_analysis(stats_data)
        self._print_detailed_problem_lists()
        self._print_final_summary(stats_data)

    def _gather_statistics(self) -> Dict[str, int]:
        """Gather all statistics into a single dictionary."""
        return {
            'total': self.stats.get_value('total_problems'),
            'existing': self.stats.get_value(
                'problems_with_existing_editorial'
            ),
            'needing': self.stats.get_value('problems_needing_editorial'),
            'generated': self.stats.get_value(
                'editorials_generated_successfully'
            ),
            'failed': self.stats.get_value('editorials_failed'),
            'admin_access': self.stats.get_value('problems_with_admin_access'),
            'existing_ac': self.stats.get_value('problems_with_existing_ac'),
            'ac_generated': self.stats.get_value('problems_ac_generated'),
            'updates_successful': self.stats.get_value(
                'website_updates_successful'
            ),
            'updates_failed': self.stats.get_value('website_updates_failed')
        }

    def _print_processing_summary(self, stats_data: Dict[str, int]) -> None:
        """Print processing summary statistics."""
        logger.info("PROCESSING SUMMARY:")
        logger.info("   Total problems processed: %d", stats_data['total'])
        logger.info("   Problems with existing editorial: %d",
                    stats_data['existing'])
        logger.info("   Problems needing editorial: %d", stats_data['needing'])
        logger.info("")

        logger.info("EDITORIAL GENERATION:")
        logger.info("   Editorials generated successfully: %d",
                    stats_data['generated'])
        logger.info("   Editorial generation failed: %d", stats_data['failed'])
        logger.info("")

    def _print_ac_solution_statistics(
        self, stats_data: Dict[str, int]
    ) -> None:
        """Print AC solution statistics."""
        logger.info("AC SOLUTION STATISTICS:")
        total = stats_data['total']
        if total > 0:
            logger.info(
                "   Problems with admin access: %d/%d "
                "(%.1f%%)", stats_data['admin_access'], total,
                stats_data['admin_access'] / total * 100)
            logger.info(
                "   Problems using existing AC solutions: %d/%d (%.1f%%)",
                stats_data['existing_ac'], total,
                stats_data['existing_ac'] / total * 100)
            logger.info("   AC solutions auto-generated: %d/%d (%.1f%%)",
                        stats_data['ac_generated'], total,
                        stats_data['ac_generated'] / total * 100)

        # Website update results
        logger.info("WEBSITE UPDATES:")
        logger.info("   Website updates successful: %d",
                    stats_data['updates_successful'])
        logger.info("   Website updates failed: %d",
                    stats_data['updates_failed'])
        logger.info("")

    def _print_success_rate_analysis(self, stats_data: Dict[str, int]) -> None:
        """Print success rate analysis."""
        needing = stats_data['needing']
        generated = stats_data['generated']

        if needing > 0:
            success_rate = (generated / needing) * 100
            logger.info("")
            logger.info("SUCCESS RATE ANALYSIS:")
            logger.info("   Success rate: %.1f%% (%d/%d)", success_rate,
                        generated, needing)
        else:
            logger.info("")
            logger.info(
                "SUCCESS RATE ANALYSIS: N/A (no problems needed editorials)"
            )

    def _print_detailed_problem_lists(self) -> None:
        """Print detailed lists of problems by category."""
        # Existing editorial problems
        existing_problems = self.stats.get_value('existing_editorial_problems')
        if existing_problems:
            logger.info("")
            logger.info(
                "PROBLEMS WITH EXISTING EDITORIALS "
                "(%d):", len(existing_problems))
            for problem in existing_problems:
                logger.info("   - %s", problem)

        # Successfully generated editorials
        generated_problems = self.stats.get_value(
            'generated_editorial_problems'
        )
        if generated_problems:
            logger.info("")
            logger.info(
                "SUCCESSFULLY GENERATED EDITORIALS "
                "(%d total):", len(generated_problems))
            for i, problem_name in enumerate(generated_problems, 1):
                logger.info("   %2d. %s", i, problem_name)

        # AC verification failed problems
        ac_failed_problems = self.stats.get_value(
            'ac_verification_failed_problems'
        )
        if ac_failed_problems:
            logger.info("")
            logger.info("AC VERIFICATION FAILED (%d):",
                        len(ac_failed_problems))
            for i, problem_name in enumerate(ac_failed_problems, 1):
                logger.info("   %2d. %s", i, problem_name)

        # Failed editorial generation problems
        failed_problems = self.stats.get_value('failed_editorial_problems')
        if failed_problems:
            logger.info("")
            logger.info(
                "FAILED EDITORIAL GENERATION "
                "(%d total):", len(failed_problems))
            for i, problem_name in enumerate(failed_problems, 1):
                logger.info("   %2d. %s", i, problem_name)

    def _print_final_summary(self, stats_data: Dict[str, int]) -> None:
        """Print final summary and completion message."""
        logger.info("=" * 80)
        logger.info("AC-Based Multi-Language Editorial Generation Complete!")
        logger.info("=" * 80)

        # Final summary
        logger.info("")
        logger.info("SUMMARY REPORT:")
        needing = stats_data['needing']
        generated = stats_data['generated']

        if needing > 0:
            logger.info(
                "   Editorial generation success rate: %.1f%% (%d/%d)",
                generated / needing * 100, generated, needing
            )
        else:
            logger.info("   No problems required editorial generation")

        logger.info("   Problems processed: %d", stats_data['total'])
        logger.info("   Admin access available: %d/%d",
                    stats_data['admin_access'], stats_data['total'])
        logger.info("   Existing AC solutions used: %d",
                    stats_data['existing_ac'])
        logger.info("   AC solutions generated: %d",
                    stats_data['ac_generated'])
        logger.info("   Multi-language editorials created: %d", generated)
        logger.info("")

        print("\n" + "=" * 80)
        print("🎯 EDITORIAL GENERATION COMPLETED!")
        print("=" * 80)
        print(f"📊 Problems processed: {stats_data['total']}")
        print(f"✅ Success: {generated}/{needing} " +
              (f"({generated/needing*100:.1f}%)" if needing > 0 else "(N/A)"))
        failed_rate = (f"({stats_data['failed']/needing*100:.1f}%)"
                       if needing > 0 else "(N/A)")
        print(f"❌ Failed: {stats_data['failed']}/{needing} " + failed_rate)
        print(f"📝 Multi-lang editorials created: {generated}")
        print(f"🔧 AC solutions generated: "
              f"{stats_data['ac_generated']}")
        print("=" * 80)


def main() -> int:
    """Main entry point for AI editorial generation."""
    if len(sys.argv) != 2:
        print("Usage: python main.py <problems_file>")
        print("")
        print("Example:")
        print("  python main.py problems_list.txt")
        print("")
        print("Features:")
        print("  1. Checks for existing editorials and skips if found")
        print("  2. Uses existing AC solutions when available")
        print("  3. Generates AC solutions if none exist")
        print("  4. Creates comprehensive editorials using AC solutions")
        print("  5. Updates editorials in 3 languages: "
              "English, Spanish, Portuguese")
        print("  6. Uploads each language separately to omegaUp")
        print("")
        return 1

    problems_file = sys.argv[1]

    try:
        orchestrator = EditorialGenerationOrchestrator()  # type: ignore
        problems = orchestrator.load_problems_from_file(problems_file)
        if not problems:
            logger.error("No problems found in file or file doesn't exist")
            return 1

        orchestrator.run_editorial_generation(problems)
        return 0

    except KeyboardInterrupt:
        logger.info("\nInterrupted by user")
        return 0
    except (ValueError, RuntimeError) as e:
        logger.error("Unexpected error: %s", str(e))
        return 1


if __name__ == "__main__":
    sys.exit(main())
