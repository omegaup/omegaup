#!/usr/bin/env python3
"""
get_user_problems.py - Extract all problems owned/created by the current user

This script authenticates with omegaUp and finds all problems where the current user
has admin access (i.e., problems they created or have admin rights to).
It saves the problem aliases to a text file for use with batch_generate_editorials.py.

Usage:
    cd stuff/editorial_generator/batch_scripts/
    python get_user_problems.py

Prerequisites:
    - .env file with OMEGAUP_USERNAME and OMEGAUP_PASSWORD in parent directory
    - Active internet connection to omegaUp

Outputs:
    - my_problems.txt: List of problem aliases (one per line)
    - logs/get_problems_YYYYMMDD_HHMMSS.log: Detailed execution log
"""

import sys
import os
import json
import logging
import requests
import time
from datetime import datetime
from pathlib import Path
from typing import List, Dict, Any, Set
from dotenv import load_dotenv

# Add parent directory to path for .env file
sys.path.insert(0, str(Path(__file__).parent.parent))

def setup_logging() -> logging.Logger:
    """Setup comprehensive logging configuration."""
    log_dir = Path(__file__).parent / "logs"
    log_dir.mkdir(exist_ok=True)

    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    log_file = log_dir / f"get_problems_{timestamp}.log"

    # Configure logging with both file and console output
    logging.basicConfig(
        level=logging.INFO,
        format='%(asctime)s - %(levelname)s - %(message)s',
        handlers=[
            logging.FileHandler(log_file, encoding='utf-8'),
            logging.StreamHandler()
        ]
    )
    
    logger = logging.getLogger(__name__)
    logger.info("=" * 80)
    logger.info("🚀 USER PROBLEMS EXTRACTION STARTED")
    logger.info("=" * 80)
    logger.info(f"📝 Log file: {log_file}")
    
    return logger

class UserProblemsExtractor:
    """Extracts all problems where the current user has admin access."""
    
    def __init__(self):
        """Initialize the extractor with authentication and configuration."""
        # Load environment variables from parent directory
        env_path = Path(__file__).parent.parent / ".env"
        load_dotenv(env_path)
        
        # API Configuration
        self.api_url = os.getenv("OMEGAUP_API_URL", "https://omegaup.com/api")
        self.base_url = os.getenv("OMEGAUP_BASE_URL", "https://omegaup.com")
        
        # Authentication credentials
        self.username = os.getenv("OMEGAUP_USERNAME")
        self.password = os.getenv("OMEGAUP_PASSWORD")
        
        if not self.username or not self.password:
            raise ValueError("OMEGAUP_USERNAME and OMEGAUP_PASSWORD must be set in .env file")
        
        # Initialize session for persistent connections and cookies
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'omegaUp-UserProblems-Extractor/1.0',
            'Accept': 'application/json',
            'Accept-Language': 'en-US,en;q=0.9,es;q=0.8,pt;q=0.7'
        })
        
        # Statistics tracking
        self.stats = {
            'api_calls_made': 0,
            'problems_checked': 0,
            'admin_problems_found': 0,
            'api_errors': 0,
            'start_time': time.time()
        }
        
        # Login for authenticated access
        self._login()
        
    def _login(self):
        """Login using the official API with username and password."""
        try:
            logger.info(f"🔐 Authenticating as {self.username}...")
            
            # Login endpoint
            login_url = f"{self.api_url}/user/login"
            login_data = {
                'usernameOrEmail': self.username,
                'password': self.password
            }
            
            # Headers for form data
            headers = {'Content-Type': 'application/x-www-form-urlencoded'}
            
            # Perform login
            response = self.session.post(
                login_url,
                data=login_data,
                headers=headers,
                timeout=(10, 30)
            )
            
            self.stats['api_calls_made'] += 1
            
            if response.status_code == 200:
                result = response.json()
                if result.get("status") == "ok":
                    logger.info("✅ Successfully authenticated with omegaUp")
                    logger.info(f"   User: {self.username}")
                    logger.info(f"   API URL: {self.api_url}")
                else:
                    raise Exception(f"Login failed: {result.get('error', 'Unknown error')}")
            else:
                raise Exception(f"HTTP {response.status_code}: Login request failed")
                
        except Exception as e:
            logger.error(f"❌ Authentication failed: {str(e)}")
            raise
    
    def _make_api_call(self, endpoint: str, params: Dict[str, Any] = None, method: str = 'GET') -> Dict[str, Any]:
        """Make an API call with error handling and logging."""
        try:
            url = f"{self.api_url}/{endpoint}"
            self.stats['api_calls_made'] += 1
            
            logger.debug(f"🌐 API Call: {method} {endpoint}")
            if params:
                logger.debug(f"   Parameters: {params}")
            
            if method == 'GET':
                response = self.session.get(url, params=params, timeout=(10, 30))
            else:
                headers = {'Content-Type': 'application/x-www-form-urlencoded'}
                response = self.session.post(url, data=params, headers=headers, timeout=(10, 30))
            
            if response.status_code != 200:
                logger.warning(f"⚠️  HTTP {response.status_code} for {endpoint}")
                self.stats['api_errors'] += 1
                return {'status': 'error', 'error': f'HTTP {response.status_code}'}
            
            result = response.json()
            
            if result.get("status") != "ok":
                logger.warning(f"⚠️  API error for {endpoint}: {result.get('error', 'Unknown')}")
                self.stats['api_errors'] += 1
                
            return result
            
        except Exception as e:
            logger.error(f"❌ API call failed for {endpoint}: {str(e)}")
            self.stats['api_errors'] += 1
            return {'status': 'error', 'error': str(e)}
    
    def get_user_created_problems(self) -> List[str]:
        """Get problems created by the current user."""
        logger.info("🔍 Searching for user-created problems...")
        
        # Use the correct API endpoint for user's own problems
        result = self._make_api_call("problem/myList", {
            'page': 1,
            'rowcount': 1000  # Get a large number of problems
        })
        
        if result.get("status") == "ok":
            problems = result.get("problems", [])
            problem_aliases = [p.get("alias") for p in problems if p.get("alias")]
            
            logger.info(f"✅ Found {len(problem_aliases)} user-created problems")
            for alias in problem_aliases:
                logger.info(f"   📝 {alias}")
                
            return problem_aliases
        else:
            error_msg = result.get('error', 'Unknown error')
            logger.warning(f"⚠️  Could not fetch user-created problems: {error_msg}")
            return []
    
    def get_admin_problems(self) -> List[str]:
        """Get problems where user has admin access using the adminList endpoint."""
        logger.info("🔍 Searching for admin-accessible problems...")
        
        admin_problems = []
        page = 1
        page_size = 100
        
        while True:
            # Use the correct adminList endpoint
            result = self._make_api_call("problem/adminList", {
                'page': page,
                'page_size': page_size,
                'query': ''  # Empty query to get all problems
            })
            
            if result.get("status") != "ok":
                error_msg = result.get('error', 'Unknown error')
                logger.warning(f"⚠️  Admin problem search failed on page {page}: {error_msg}")
                break
            
            problems = result.get("problems", [])
            if not problems:
                logger.info(f"📄 No more problems found at page {page}")
                break
            
            logger.info(f"📄 Found page {page}: {len(problems)} admin problems")
            
            # Add all problems from this page
            for problem in problems:
                alias = problem.get("alias")
                if alias:
                    admin_problems.append(alias)
                    logger.info(f"   ✅ Admin problem: {alias}")
                    self.stats['problems_checked'] += 1
            
            # Check if we should continue
            if len(problems) < page_size:
                logger.info("📄 Reached end of admin problem list")
                break
                
            page += 1
            
            # Rate limiting - don't overwhelm the API
            time.sleep(0.5)
        
        logger.info(f"🎯 Found {len(admin_problems)} problems with admin access")
        return admin_problems
    
    def extract_all_problems(self) -> List[str]:
        """Extract all problems where the user has admin access."""
        logger.info("🚀 Starting comprehensive problem extraction...")
        
        all_problems = set()
        
        # Method 1: Get user-created problems
        logger.info("\n" + "="*60)
        logger.info("METHOD 1: User-created problems (myList)")
        logger.info("="*60)
        
        created_problems = self.get_user_created_problems()
        all_problems.update(created_problems)
        self.stats['admin_problems_found'] += len(created_problems)
        
        # Method 2: Get admin-accessible problems
        logger.info("\n" + "="*60)
        logger.info("METHOD 2: Admin-accessible problems (adminList)")
        logger.info("="*60)
        
        admin_problems = self.get_admin_problems()
        
        # Add new admin problems that aren't already in our list
        new_admin_problems = 0
        for alias in admin_problems:
            if alias not in all_problems:
                all_problems.add(alias)
                new_admin_problems += 1
                logger.info(f"   ✅ Additional admin problem: {alias}")
        
        self.stats['admin_problems_found'] += new_admin_problems
        
        final_list = sorted(list(all_problems))
        
        logger.info("\n" + "="*60)
        logger.info("EXTRACTION COMPLETE")
        logger.info("="*60)
        logger.info(f"📊 User-created problems: {len(created_problems)}")
        logger.info(f"📊 Additional admin problems: {new_admin_problems}")
        logger.info(f"📊 Total problems with admin access: {len(final_list)}")
        
        return final_list
    
    def save_problems_to_file(self, problems: List[str], filename: str = "my_problems.txt") -> None:
        """Save problem list to text file."""
        try:
            output_path = Path(__file__).parent / filename
            
            logger.info(f"💾 Saving {len(problems)} problems to {output_path}")
            
            with open(output_path, 'w', encoding='utf-8') as f:
                f.write("# Problems where current user has admin access\n")
                f.write(f"# Generated on {datetime.now().isoformat()}\n")
                f.write(f"# User: {self.username}\n")
                f.write(f"# Total problems: {len(problems)}\n")
                f.write("#\n")
                f.write("# One problem alias per line\n")
                f.write("# Lines starting with # are comments\n")
                f.write("\n")
                
                for problem in problems:
                    f.write(f"{problem}\n")
            
            logger.info(f"✅ Successfully saved problems to {filename}")
            logger.info(f"   File location: {output_path.absolute()}")
            
        except Exception as e:
            logger.error(f"❌ Failed to save problems to file: {str(e)}")
            raise
    
    def print_final_statistics(self) -> None:
        """Print comprehensive final statistics."""
        duration = time.time() - self.stats['start_time']
        
        logger.info("\n" + "="*80)
        logger.info("📊 FINAL STATISTICS")
        logger.info("="*80)
        logger.info(f"⏱️  Total Runtime: {duration:.1f} seconds")
        logger.info(f"🌐 API Calls Made: {self.stats['api_calls_made']}")
        logger.info(f"🔍 Problems Checked: {self.stats['problems_checked']}")
        logger.info(f"✅ Admin Problems Found: {self.stats['admin_problems_found']}")
        logger.info(f"❌ API Errors: {self.stats['api_errors']}")
        
        if self.stats['api_calls_made'] > 0:
            success_rate = ((self.stats['api_calls_made'] - self.stats['api_errors']) / 
                          self.stats['api_calls_made'] * 100)
            logger.info(f"📈 API Success Rate: {success_rate:.1f}%")
        
        logger.info("="*80)
        logger.info("🎯 USER PROBLEMS EXTRACTION COMPLETED")
        logger.info("="*80)

def main():
    """Main entry point for user problems extraction."""
    global logger
    logger = setup_logging()
    
    try:
        # Initialize extractor
        extractor = UserProblemsExtractor()
        
        # Extract all problems
        problems = extractor.extract_all_problems()
        
        if not problems:
            logger.warning("⚠️  No problems found with admin access")
            logger.info("💡 Make sure you have created problems or have admin access to existing ones")
            return 1
        
        # Save to file
        extractor.save_problems_to_file(problems)
        
        # Print statistics
        extractor.print_final_statistics()
        
        print("\n🎉 SUCCESS! Problem extraction completed.")
        print(f"📝 Found {len(problems)} problems saved to my_problems.txt")
        print("🚀 Ready to run: python batch_generate_editorials.py")
        
        return 0
        
    except KeyboardInterrupt:
        logger.info("\n⏹️  Interrupted by user")
        return 0
    except Exception as e:
        logger.error(f"💥 Unexpected error: {str(e)}")
        return 1

if __name__ == "__main__":
    sys.exit(main())
