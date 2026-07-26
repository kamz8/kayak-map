/**
 * Trail Difficulty Composable
 */

export const DIFFICULTY_CONFIG = {
  'łatwy': {
    label: 'Łatwy',
    description: 'Spokojne płynięcie, brak trudności',
    color: 'success'
  },
  'umiarkowany': {
    label: 'Umiarkowany',
    description: 'Średnia trudność, wymaga podstawowego doświadczenia',
    color: 'warning'
  },
  'trudny': {
    label: 'Trudny',
    description: 'Wysokie wymagania techniczne i kondycyjne',
    color: 'error'
  }
}

export function useTrailDifficulty() {
  const getDifficultyConfig = (difficulty) => {
    return DIFFICULTY_CONFIG[difficulty] || {
      label: difficulty,
      description: 'Nieznany poziom',
      color: 'grey'
    }
  }

  const getDifficultyLabel = (difficulty) => {
    return getDifficultyConfig(difficulty).label
  }

  const getDifficultyColor = (difficulty) => {
    return getDifficultyConfig(difficulty).color
  }

  const getDifficultyOptions = () => {
    return Object.keys(DIFFICULTY_CONFIG).map(key => ({
      value: key,
      title: DIFFICULTY_CONFIG[key].label
    }))
  }

  return {
    DIFFICULTY_CONFIG,
    getDifficultyConfig,
    getDifficultyLabel,
    getDifficultyColor,
    getDifficultyOptions
  }
}