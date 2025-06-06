<template>
  <div class="solutions-container">
    <div class="header-section">
      <h2 class="solutions-title">All Solutions</h2>
      
      <!-- Filter and Sort Options -->
      <div class="filter-options">
        <div class="sort-dropdown">
          <label for="sort-select">Sort by: </label>
          <select id="sort-select" v-model="currentSort" @change="sortSolutions" class="sort-select">
            <option value="most-voted">Most Voted</option>
            <option value="newest">Newest First</option>
            <option value="oldest">Oldest First</option>
            <option value="most-comments">Most Comments</option>
          </select>
        </div>
        
        <div class="filter-dropdown">
          <label for="language-filter">Language: </label>
          <select id="language-filter" v-model="languageFilter" @change="filterSolutions" class="filter-select">
            <option value="all">All Languages</option>
            <option v-for="lang in availableLanguages" :key="lang" :value="lang">{{ lang }}</option>
          </select>
        </div>
        
        <!-- <div class="filter-dropdown">
          <label for="approach-filter">Approach: </label>
          <select id="approach-filter" v-model="approachFilter" @change="filterSolutions" class="filter-select">
            <option value="all">All Approaches</option>
            <option v-for="approach in availableApproaches" :key="approach" :value="approach">{{ approach }}</option>
          </select>
        </div> -->
      </div>
    </div>
    
    <!-- Results Summary -->
    <div class="results-summary">
      <p>Showing {{ filteredSolutions.length }} of {{ solutions.length }} solutions</p>
      <button @click="resetFilters" class="reset-filters-btn" v-if="isFiltered">Reset Filters</button>
    </div>
    
    <!-- Solutions displayed vertically -->
    <div v-for="(solution, index) in filteredSolutions" :key="solution.id" class="solution-card">
      <!-- User Info Section -->
      <div class="user-info">
        <div class="avatar-container">
          <div class="avatar">
            <span class="avatar-letter">{{ solution.userName.charAt(0) }}</span>
          </div>
        </div>
        <div class="user-details">
          <div class="username">{{ solution.userName }}</div>
          <div class="reputation"></div>
        </div>
        <div class="time-posted">Posted {{ solution.timePosted }}</div>
        <button class="report-solution-btn" @click="reportSolution(index)">Report Solution</button>
      </div>

      <!-- Approach and Complexity Section -->
      <div class="approach-complexity-section">
        <div class="approach">
          <span class="approach-label">Approach: {{ solution.approach }}</span>
        </div>
        <div class="complexity">
          <span class="time-complexity">
            <i class="time-icon"></i> Time: {{ solution.timeComplexity }}
          </span>
          <span class="space-complexity">
            <i class="space-icon"></i> Space: {{ solution.spaceComplexity }}
          </span>
        </div>
      </div>

      <!-- Solution Code Section -->
      <div class="solution-section">
        <div class="solution-header">
          <span class="language-label">Solution in {{ solution.language }}</span>
          <button class="toggle-code-btn" @click="toggleCode(solution.id)">
            {{ codeVisibility[solution.id] ? 'Hide Code' : 'Show Code' }}
          </button>
        </div>
        <div v-if="codeVisibility[solution.id]" class="code-block">
          <pre><code>{{ solution.solutionCode }}</code></pre>
        </div>
      </div>

      <!-- Explanation Section -->
      <div class="explanation-section">
        <h4>Explanation</h4>
        <p>{{ solution.explanation }}</p>
      </div>

      <!-- Voting and Comments Section -->
      <div class="interaction-section">
        <div class="voting">
          <button class="upvote-btn" @click="handleUpvote(solution.id)">
            <i class="thumbs-up-icon"></i> {{ solution.upvotes }}
          </button>
          <button class="downvote-btn" @click="handleDownvote(solution.id)">
            <i class="thumbs-down-icon"></i> {{ solution.downvotes }}
          </button>
        </div>
        <div class="comments-count" @click="toggleComments(solution.id)">
          <i class="comments-icon"></i> {{ solution.comments.length }} Comments
        </div>
      </div>

      <!-- Comments Section -->
      <div v-if="commentsVisibility[solution.id]" class="comments-section">
        <div v-for="(comment, commentIndex) in solution.comments" :key="commentIndex" class="comment">
          <div class="comment-header">
            <span class="comment-author">{{ comment.author }}</span>
            <span class="comment-date">{{ comment.date }}</span>
          </div>
          <div class="comment-content">{{ comment.content }}</div>
        </div>
        
        <!-- Add New Comment -->
        <div class="add-comment">
          <textarea 
            v-model="newComments[solution.id]" 
            placeholder="Add a comment..." 
            class="comment-input"
          ></textarea>
          <button class="post-comment-btn" @click="postComment(solution.id)">Post</button>
        </div>
      </div>
    </div>
    
    <!-- Empty State -->
    <div v-if="filteredSolutions.length === 0" class="empty-state">
      <div class="empty-icon">🔍</div>
      <h3>No solutions match your filters</h3>
      <p>Try adjusting your filters or <button @click="resetFilters" class="reset-link">reset all filters</button></p>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

interface Comment {
  id: number;
  author: string;
  date: string;
  content: string;
}

interface Solution {
  id: number;
  userName: string;
  reputation: number;
  approach: string;
  timeComplexity: string;
  spaceComplexity: string;
  language: string;
  solutionCode: string;
  explanation: string;
  upvotes: number;
  downvotes: number;
  comments: Comment[];
  timePosted: string;
  dateObj: Date;
}

@Component
export default class OfficialSolutions extends Vue {
  // Filter and sort state
  currentSort = 'most-voted';
  languageFilter = 'all';
  approachFilter = 'all';
  
  // UI state tracking
  codeVisibility: { [key: number]: boolean } = {};
  commentsVisibility: { [key: number]: boolean } = {};
  newComments: { [key: number]: string } = {};
  
  // Filtered solutions
  filteredSolutions: Solution[] = [];

  created() {
    // Add IDs and date objects to solutions for easier management
    this.solutions.forEach((solution, index) => {
      solution.id = index + 1;
      
      // Convert timePosted string to Date object for sorting
      const timeAgo = solution.timePosted;
      const now = new Date();
      let dateObj = new Date(now);
      
      if (timeAgo.includes('days')) {
        const days = parseInt(timeAgo);
        dateObj.setDate(now.getDate() - days);
      } else if (timeAgo.includes('hours')) {
        const hours = parseInt(timeAgo);
        dateObj.setHours(now.getHours() - hours);
      } else if (timeAgo.includes('minutes')) {
        const minutes = parseInt(timeAgo);
        dateObj.setMinutes(now.getMinutes() - minutes);
      }
      
      solution.dateObj = dateObj;
      
      // Initialize visibility state
      this.codeVisibility[solution.id] = true;
      this.commentsVisibility[solution.id] = false;
      this.newComments[solution.id] = '';
    });
    
    // Initial filtering and sorting
    this.filterAndSortSolutions();
  }
  
  // Computed properties for filters
  get availableLanguages(): string[] {
    const languages = new Set<string>();
    this.solutions.forEach(solution => languages.add(solution.language));
    return Array.from(languages);
  }
  
  get availableApproaches(): string[] {
    const approaches = new Set<string>();
    this.solutions.forEach(solution => approaches.add(solution.approach));
    return Array.from(approaches);
  }
  
  get isFiltered(): boolean {
    return this.languageFilter !== 'all' || this.approachFilter !== 'all';
  }

  // Filter and sort methods
  filterSolutions() {
    this.filterAndSortSolutions();
  }
  
  sortSolutions() {
    this.filterAndSortSolutions();
  }
  
  resetFilters() {
    this.languageFilter = 'all';
    this.approachFilter = 'all';
    this.filterAndSortSolutions();
  }
  
  filterAndSortSolutions() {
    // Filter solutions
    let result = [...this.solutions];
    
    if (this.languageFilter !== 'all') {
      result = result.filter(solution => solution.language === this.languageFilter);
    }
    
    if (this.approachFilter !== 'all') {
      result = result.filter(solution => solution.approach === this.approachFilter);
    }
    
    // Sort solutions
    switch (this.currentSort) {
      case 'most-voted':
        result.sort((a, b) => (b.upvotes - b.downvotes) - (a.upvotes - a.downvotes));
        break;
      case 'newest':
        result.sort((a, b) => b.dateObj.getTime() - a.dateObj.getTime());
        break;
      case 'oldest':
        result.sort((a, b) => a.dateObj.getTime() - b.dateObj.getTime());
        break;
      case 'most-comments':
        result.sort((a, b) => b.comments.length - a.comments.length);
        break;
    }
    
    this.filteredSolutions = result;
  }

  // UI interaction methods
  toggleCode(solutionId: number) {
    this.codeVisibility[solutionId] = !this.codeVisibility[solutionId];
  }

  toggleComments(solutionId: number) {
    this.commentsVisibility[solutionId] = !this.commentsVisibility[solutionId];
  }

  handleUpvote(solutionId: number) {
    const solution = this.solutions.find(s => s.id === solutionId);
    if (solution) {
      solution.upvotes++;
      this.filterAndSortSolutions(); // Re-sort if needed
    }
  }

  handleDownvote(solutionId: number) {
    const solution = this.solutions.find(s => s.id === solutionId);
    if (solution) {
      solution.downvotes++;
      this.filterAndSortSolutions(); // Re-sort if needed
    }
  }

  postComment(solutionId: number) {
    const solution = this.solutions.find(s => s.id === solutionId);
    if (solution && this.newComments[solutionId].trim()) {
      solution.comments.push({
        id: solution.comments.length + 1,
        author: 'You',
        date: 'Just now',
        content: this.newComments[solutionId]
      });
      this.newComments[solutionId] = '';
      this.filterAndSortSolutions(); // Re-sort if needed
    }
  }

  reportSolution(index: number) {
    alert(`Solution has been reported. Our moderators will review it shortly.`);
  }

  // Sample solutions data with added timePosted field
  solutions: Solution[] = [
    {
      id: 0, // Will be set in created()
      userName: 'K Vijay',
      reputation: 15642,
      approach: 'Hash Map',
      timeComplexity: 'O(n)',
      spaceComplexity: 'O(n)',
      language: 'Java',
      solutionCode: `class Solution {
    public int[] twoSum(int[] nums, int target) {
        Map<Integer, Integer> map = new HashMap<>();
        for (int i = 0; i < nums.length; i++) {
            int complement = target - nums[i];
            if (map.containsKey(complement)) {
                return new int[] { map.get(complement), i };
            }
            map.put(nums[i], i);
        }
        return new int[]{};
    }
}`,
      explanation: 'This solution uses a hash map to efficiently find two numbers that add up to the target. We iterate through the array once, for each number checking if its complement exists in the map. If found, we return the indices; otherwise, we add the current number to the map.',
      upvotes: 156,
      downvotes: 12,
      timePosted: '2 days ago',
      dateObj: new Date(), // Will be set in created()
      comments: [
        {
          id: 1,
          author: 'Shashi',
          date: '2 days ago',
          content: 'Great solution! Very clean implementation.'
        },
        {
          id: 2,
          author: 'Sagar',
          date: '1 day ago',
          content: 'Have you considered using a TreeMap instead? It might be more efficient for certain inputs.'
        },
        {
          id: 3,
          author: 'Suveer',
          date: '5 hours ago',
          content: 'This helped me understand the approach better, thanks for sharing!'
        }
      ]
    },
    {
      id: 0, // Will be set in created()
      userName: 'Sarah Chen',
      reputation: 9875,
      approach: 'Two Pointers',
      timeComplexity: 'O(n log n)',
      spaceComplexity: 'O(n)',
      language: 'Python',
      solutionCode: `class Solution:
    def twoSum(self, nums: List[int], target: int) -> List[int]:
        # Create a copy with indices
        nums_with_indices = [(nums[i], i) for i in range(len(nums))]
        
        # Sort by value
        nums_with_indices.sort(key=lambda x: x[0])
        
        # Two pointers approach
        left, right = 0, len(nums) - 1
        while left < right:
            curr_sum = nums_with_indices[left][0] + nums_with_indices[right][0]
            if curr_sum == target:
                return [nums_with_indices[left][1], nums_with_indices[right][1]]
            elif curr_sum < target:
                left += 1
            else:
                right -= 1
                
        return []  # No solution found`,
      explanation: 'This solution uses the two-pointers technique. First, we create a list of (value, index) pairs to keep track of original indices. Then we sort this list by values and use two pointers (left and right) to find a pair that sums to the target. This approach has a time complexity of O(n log n) due to sorting.',
      upvotes: 112,
      downvotes: 18,
      timePosted: '12 hours ago',
      dateObj: new Date(), // Will be set in created()
      comments: [
        {
          id: 1,
          author: 'Mark Williams',
          date: '3 days ago',
          content: 'The two pointers approach is elegant, but the hashmap solution is faster for this problem.'
        },
        {
          id: 2,
          author: 'Priya Sharma',
          date: '1 day ago',
          content: 'Thanks for including the original indices tracking - that\'s the tricky part here!'
        }
      ]
    },
    {
      id: 0, // Will be set in created()
      userName: 'Michael Lee',
      reputation: 21389,
      approach: 'Brute Force',
      timeComplexity: 'O(n²)',
      spaceComplexity: 'O(1)',
      language: 'JavaScript',
      solutionCode: `/**
 * @param {number[]} nums
 * @param {number} target
 * @return {number[]}
 */
var twoSum = function(nums, target) {
    for (let i = 0; i < nums.length; i++) {
        for (let j = i + 1; j < nums.length; j++) {
            if (nums[i] + nums[j] === target) {
                return [i, j];
            }
        }
    }
    return [];
};`,
      explanation: 'This is a straightforward brute force approach that checks every possible pair of numbers in the array. For each element, we iterate through the rest of the array to find a complement that adds up to the target. While simple to understand, this solution has a time complexity of O(n²), making it less efficient for large inputs.',
      upvotes: 42,
      downvotes: 87,
      timePosted: '5 days ago',
      dateObj: new Date(), // Will be set in created()
      comments: [
        {
          id: 1,
          author: 'Elena Rodriguez',
          date: '4 days ago',
          content: 'This works but will time out on large inputs. Good for understanding the problem though.'
        },
        {
          id: 2,
          author: 'Tom Anderson',
          date: '2 days ago',
          content: 'I appreciate seeing the brute force approach alongside optimized solutions. Helps to understand the trade-offs.'
        }
      ]
    },
    {
      id: 0, // Will be set in created()
      userName: 'Aditya Patel',
      reputation: 17632,
      approach: 'Binary Search',
      timeComplexity: 'O(n log n)',
      spaceComplexity: 'O(n)',
      language: 'C++',
      solutionCode: `class Solution {
public:
    vector<int> twoSum(vector<int>& nums, int target) {
        // Create a copy with indices
        vector<pair<int, int>> numWithIdx;
        for (int i = 0; i < nums.size(); i++) {
            numWithIdx.push_back({nums[i], i});
        }
        
        // Sort by value
        sort(numWithIdx.begin(), numWithIdx.end());
        
        // For each element, binary search for its complement
        for (int i = 0; i < numWithIdx.size(); i++) {
            int complement = target - numWithIdx[i].first;
            
            // Binary search for complement
            int left = i + 1, right = numWithIdx.size() - 1;
            while (left <= right) {
                int mid = left + (right - left) / 2;
                
                if (numWithIdx[mid].first == complement) {
                    return {numWithIdx[i].second, numWithIdx[mid].second};
                } else if (numWithIdx[mid].first < complement) {
                    left = mid + 1;
                } else {
                    right = mid - 1;
                }
            }
        }
        
        return {};  // No solution found
    }
};`,
      explanation: 'This solution combines sorting with binary search. First, we create pairs of (value, index) to preserve original indices. After sorting these pairs, for each element, we use binary search to find its complement. This approach has O(n log n) time complexity from sorting and the n binary searches that each take O(log n) time.',
      upvotes: 89,
      downvotes: 23,
      timePosted: '30 minutes ago',
      dateObj: new Date(), // Will be set in created()
      comments: [
        {
          id: 1,
          author: 'David Kim',
          date: '5 days ago',
          content: 'Interesting approach with binary search. Still not as efficient as the hash map for this particular problem though.'
        },
        {
          id: 2,
          author: 'Sophia Zhang',
          date: '3 days ago',
          content: 'The code is clean but remember that we could also do a single pass with a hash map for O(n) time complexity.'
        },
        {
          id: 3,
          author: 'Raj Malhotra',
          date: '1 day ago',
          content: 'Great implementation! The binary search approach shows a different way of thinking about the problem.'
        }
      ]
    }
  ];
}
</script>

<style scoped>
.solutions-container {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
  padding: 20px 0;
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 12px;
}

.solutions-title {
  font-size: 24px;
  font-weight: 600;
  color: #333;
  margin: 0;
}

/* Filter and Sort Options */
.filter-options {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.sort-dropdown, .filter-dropdown {
  display: flex;
  align-items: center;
  gap: 8px;
}

.sort-select, .filter-select {
  padding: 6px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  color: #333;
  background-color: white;
}

/* Results Summary */
.results-summary {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  color: #666;
  font-size: 14px;
}

.reset-filters-btn {
  background: none;
  border: 1px solid #ddd;
  color: #0066cc;
  border-radius: 4px;
  padding: 4px 12px;
  cursor: pointer;
  font-size: 14px;
}

.solution-card {
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 24px;
  overflow: hidden;
  background-color: #fff;
}

/* User Info Styling */
.user-info {
  display: flex;
  padding: 16px;
  align-items: center;
  border-bottom: 1px solid #eee;
}

.avatar-container {
  margin-right: 12px;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #3f51b5;
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-letter {
  color: white;
  font-size: 18px;
  font-weight: 500;
}

.user-details {
  flex-grow: 1;
}

.user-details .username {
  font-weight: 500;
  font-size: 16px;
  color: #333;
}

.user-details .reputation {
  font-size: 12px;
  color: #666;
}

.time-posted {
  margin-right: 16px;
  font-size: 14px;
  color: #777;
}

.report-solution-btn {
  background: none;
  border: 1px solid #ddd;
  color: #c00;
  border-radius: 4px;
  padding: 6px 12px;
  cursor: pointer;
  font-size: 12px;
}

.report-solution-btn:hover {
  background-color: #fff0f0;
}

/* Approach and Complexity Section */
.approach-complexity-section {
  background-color: #f0f7ff;
  padding: 12px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
}

.approach {
  font-weight: 500;
  color: #0066cc;
}

.complexity {
  display: flex;
  gap: 16px;
}

.time-complexity, .space-complexity {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #444;
}

.time-icon::before {
  content: "⏱";
  margin-right: 4px;
}

.space-icon::before {
  content: "📊";
  margin-right: 4px;
}

/* Solution Code Section */
.solution-section {
  border-bottom: 1px solid #eee;
}

.solution-header {
  padding: 12px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #f5f5f5;
}

.language-label {
  font-weight: 500;
  color: #333;
}

.toggle-code-btn {
  background-color: #fff;
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 4px 12px;
  cursor: pointer;
  font-size: 12px;
}

.code-block {
  padding: 0;
  margin: 0;
  background-color: #f8f9fa;
}

.code-block pre {
  margin: 0;
  padding: 16px;
  overflow-x: auto;
}

.code-block code {
  font-family: 'Consolas', 'Monaco', monospace;
  font-size: 14px;
  line-height: 1.5;
  color: #333;
}

/* Explanation Section */
.explanation-section {
  padding: 16px;
  background-color: #f0f7ff;
  border-bottom: 1px solid #ddd;
}

.explanation-section h4 {
  margin-top: 0;
  margin-bottom: 8px;
  font-size: 16px;
  color: #333;
}

.explanation-section p {
  margin: 0;
  line-height: 1.5;
  color: #444;
}

/* Voting and Comments Section */
.interaction-section {
  padding: 12px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #f9f9f9;
}

.voting {
  display: flex;
  gap: 12px;
}

.upvote-btn, .downvote-btn {
  background: none;
  border: none;
  display: flex;
  align-items: center;
  cursor: pointer;
  color: #555;
  font-size: 14px;
}

.thumbs-up-icon::before {
  content: "👍";
  margin-right: 4px;
}

.thumbs-down-icon::before {
  content: "👎";
  margin-right: 4px;
}

.comments-count {
  cursor: pointer;
  color: #555;
  font-size: 14px;
  display: flex;
  align-items: center;
}

.comments-icon::before {
  content: "💬";
  margin-right: 4px;
}

/* Comments Section */
.comments-section {
  padding: 16px;
  border-top: 1px solid #eee;
  background-color: #fafafa;
}

.comment {
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #eee;
}

.comment:last-child {
  margin-bottom: 0;
  padding-bottom: 0;
  border-bottom: none;
}

.comment-header {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
}

.comment-author {
  font-weight: 500;
  margin-right: 8px;
}

.comment-date {
  font-size: 12px;
  color: #777;
}

.comment-content {
  line-height: 1.5;
  color: #333;
}

/* Add Comment Section */
.add-comment {
  margin-top: 16px;
  display: flex;
  flex-direction: column;
}

.comment-input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  margin-bottom: 8px;
  resize: vertical;
  min-height: 60px;
}

.post-comment-btn {
  align-self: flex-end;
  background-color: #0066cc;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 6px 16px;
  cursor: pointer;
  font-weight: 500;
}

.post-comment-btn:hover {
  background-color: #0052a3;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 40px 20px;
  border: 1px dashed #ddd;
  border-radius: 8px;
  margin: 20px 0;
  color: #666;
}

.empty-icon {
  font-size: 40px;
  margin-bottom: 16px;
}

.empty-state h3 {
  font-size: 18px;
  margin-bottom: 12px;
  color: #333;
}

.reset-link {
  color: #0066cc;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  text-decoration: underline;
}

@media (max-width: 768px) {
  .header-section {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .filter-options {
    width: 100%;
    margin-top: 12px;
  }
  
  .approach-complexity-section {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .complexity {
    width: 100%;
    justify-content: space-between;
  }
  
  .user-info {
    flex-wrap: wrap;
  }
  
  .time-posted {
    order: 3;
  }
}
</style>