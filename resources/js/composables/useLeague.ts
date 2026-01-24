import { ref, computed } from 'vue';
import { api } from '@/lib/api';

interface Team {
  id: number;
  name: string;
  power: number;
  logo: string | null;
}

interface Match {
  id: number;
  home_team_id: number;
  away_team_id: number;
  home_team: string;
  away_team: string;
  home_score: number | null;
  away_score: number | null;
  played: boolean;
  week: number;
}

interface WeekData {
  week: number;
  matches: Match[];
}

interface LeagueStanding {
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
}

interface Prediction {
  team_id: number;
  team_name: string;
  probability: number;
}

interface CurrentWeek {
  last_played_week: number | null;
  next_week: number | null;
  total_weeks: number;
}

export function useLeague() {
  const teams = ref<Team[]>([]);
  const matches = ref<WeekData[]>([]);
  const leagueTable = ref<LeagueStanding[]>([]);
  const predictions = ref<Prediction[]>([]);
  const currentWeek = ref<CurrentWeek>({
    last_played_week: null,
    next_week: null,
    total_weeks: 0,
  });

  const loading = ref(false);
  const error = ref<string | null>(null);

  const hasFixtures = computed(() => matches.value.length > 0);
  const canPlayNextWeek = computed(
    () => currentWeek.value.next_week !== null && !loading.value
  );
  const allGamesPlayed = computed(
    () =>
      currentWeek.value.total_weeks > 0 &&
      currentWeek.value.next_week === null
  );
  const showPredictions = computed(
    () =>
      currentWeek.value.last_played_week !== null &&
      currentWeek.value.last_played_week >= 4
  );

  const setError = (message: string | null) => {
    error.value = message;
  };

  const setLoading = (isLoading: boolean) => {
    loading.value = isLoading;
  };

  const loadTeams = async () => {
    try {
      setLoading(true);
      setError(null);
      teams.value = await api.getTeams();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load teams');
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const loadMatches = async () => {
    try {
      setLoading(true);
      setError(null);
      const data = await api.getMatches();
      matches.value = data.weeks;
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load matches');
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const loadLeagueTable = async () => {
    try {
      setLoading(true);
      setError(null);
      leagueTable.value = await api.getLeagueTable();
    } catch (err) {
      setError(
        err instanceof Error ? err.message : 'Failed to load league table'
      );
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const loadPredictions = async () => {
    try {
      setLoading(true);
      setError(null);
      const data = await api.getPredictions();
      if (Array.isArray(data)) {
        predictions.value = data;
      } else {
        predictions.value = [];
      }
    } catch (err) {
      setError(
        err instanceof Error ? err.message : 'Failed to load predictions'
      );
      predictions.value = [];
    } finally {
      setLoading(false);
    }
  };

  const loadCurrentWeek = async () => {
    try {
      setLoading(true);
      setError(null);
      currentWeek.value = await api.getCurrentWeek();
    } catch (err) {
      setError(
        err instanceof Error ? err.message : 'Failed to load current week'
      );
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const generateFixtures = async () => {
    try {
      setLoading(true);
      setError(null);
      await api.generateFixtures();
      await Promise.all([
        loadMatches(),
        loadLeagueTable(),
        loadCurrentWeek(),
      ]);
    } catch (err) {
      setError(
        err instanceof Error
          ? err.message
          : 'Failed to generate fixtures'
      );
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const simulateNextWeek = async () => {
    try {
      setLoading(true);
      setError(null);
      const result = await api.simulateNextWeek();
      leagueTable.value = result.table as LeagueStanding[];
      if (result.predictions && Array.isArray(result.predictions)) {
        predictions.value = result.predictions as Prediction[];
      }
      await Promise.all([loadMatches(), loadCurrentWeek()]);
    } catch (err) {
      setError(
        err instanceof Error ? err.message : 'Failed to simulate week'
      );
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const simulateAll = async () => {
    try {
      setLoading(true);
      setError(null);
      const result = await api.simulateAll();
      leagueTable.value = result.table as LeagueStanding[];
      if (result.predictions && Array.isArray(result.predictions)) {
        predictions.value = result.predictions as Prediction[];
      }
      await Promise.all([loadMatches(), loadCurrentWeek()]);
    } catch (err) {
      setError(
        err instanceof Error ? err.message : 'Failed to simulate all games'
      );
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const resetLeague = async () => {
    try {
      setLoading(true);
      setError(null);
      await api.resetLeague();
      await Promise.all([
        loadMatches(),
        loadLeagueTable(),
        loadCurrentWeek(),
        loadPredictions(),
      ]);
    } catch (err) {
      setError(
        err instanceof Error ? err.message : 'Failed to reset league'
      );
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const refreshAll = async () => {
    await Promise.all([
      loadTeams(),
      loadMatches(),
      loadLeagueTable(),
      loadCurrentWeek(),
      loadPredictions(),
    ]);
  };

  return {
    teams,
    matches,
    leagueTable,
    predictions,
    currentWeek,
    loading,
    error,
    hasFixtures,
    canPlayNextWeek,
    allGamesPlayed,
    showPredictions,
    loadTeams,
    loadMatches,
    loadLeagueTable,
    loadPredictions,
    loadCurrentWeek,
    generateFixtures,
    simulateNextWeek,
    simulateAll,
    resetLeague,
    refreshAll,
    setError,
  };
}
