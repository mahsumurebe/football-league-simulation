const API_BASE_URL = '/api/league';

interface StandardApiResponse<T> {
  success: boolean;
  data?: T;
  message?: string;
  error?: string;
  errors?: Record<string, string[]>;
}

async function apiRequest<T>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  try {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...options.headers,
      },
      ...options,
    });

    const result: StandardApiResponse<T> = await response.json();

    if (!response.ok || !result.success) {
      const errorMessage =
        result.message ||
        result.error ||
        (result.errors
          ? Object.values(result.errors).flat().join(', ')
          : 'API request failed');
      throw new Error(errorMessage);
    }

    return result.data !== undefined ? result.data : (result as unknown as T);
  } catch (error) {
    if (error instanceof Error) {
      throw error;
    }
    throw new Error('Unknown error occurred');
  }
}

export const api = {
  /**
   * Generate fixtures for existing teams
   */
  async generateFixtures() {
    return apiRequest<{
      fixtures_count: number;
      weeks: number;
      team_count: number;
    }>('/generate-fixtures', {
      method: 'POST',
    });
  },

  /**
   * Get all teams
   */
  async getTeams() {
    return apiRequest<
      Array<{
        id: number;
        name: string;
        power: number;
        logo: string | null;
      }>
    >('/teams');
  },

  /**
   * Get all matches grouped by week
   */
  async getMatches() {
    return apiRequest<{
      weeks: Array<{
        week: number;
        matches: Array<{
          id: number;
          home_team_id: number;
          away_team_id: number;
          home_team: string;
          away_team: string;
          home_score: number | null;
          away_score: number | null;
          played: boolean;
          week: number;
        }>;
      }>;
    }>('/matches');
  },

  /**
   * Get current week information
   */
  async getCurrentWeek() {
    return apiRequest<{
      last_played_week: number | null;
      next_week: number | null;
      total_weeks: number;
    }>('/current-week');
  },

  /**
   * Get league table
   */
  async getLeagueTable() {
    return apiRequest<
      Array<{
        id: number;
        team_id: number;
        played: number;
        won: number;
        drawn: number;
        lost: number;
        goals_for: number;
        goals_against: number;
        goal_difference: number;
        points: number;
        team: {
          id: number;
          name: string;
          power: number;
        };
      }>
    >('/table');
  },

  /**
   * Get matches by week
   */
  async getMatchesByWeek(week: number) {
    return apiRequest<
      Array<{
        home_team: string;
        away_team: string;
        score: string;
        played: boolean;
      }>
    >(`/matches/week/${week}`);
  },

  /**
   * Simulate next week
   */
  async simulateNextWeek() {
    return apiRequest<{
      week: number;
      results: Array<unknown>;
      table: Array<unknown>;
      predictions: Array<unknown>;
    }>('/simulate-week', {
      method: 'POST',
    });
  },

  /**
   * Simulate all remaining games
   */
  async simulateAll() {
    return apiRequest<{
      results: Array<unknown>;
      table: Array<unknown>;
      predictions: Array<unknown>;
    }>('/simulate-all', {
      method: 'POST',
    });
  },

  /**
   * Get predictions
   */
  async getPredictions() {
    return apiRequest<
      | Array<{
          team_id: number;
          team_name: string;
          probability: number;
        }>
      | null
    >('/predictions');
  },

  /**
   * Reset league
   */
  async resetLeague() {
    return apiRequest<null>('/reset', {
      method: 'POST',
    });
  },

  /**
   * Update game scores
   */
  async updateGame(
    id: number,
    data: {
      home_score?: number | null;
      away_score?: number | null;
    }
  ) {
    return apiRequest<{
      game: {
        id: number;
        home_team: string;
        away_team: string;
        home_score: number | null;
        away_score: number | null;
        week: number;
        played: boolean;
      };
      table: Array<unknown>;
    }>(`/matches/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  },
};
