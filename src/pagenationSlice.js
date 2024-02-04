import { createSlice } from '@reduxjs/toolkit';

const pagenationState = {
  currentPage: 0 // 現在のページとページネーションを表す数
};

export const pagenationSlice = createSlice({
  name: 'pagenation',
  initialState: pagenationState,
  reducers: {
    nextPagenation: (state) => {
      state.currentPage += 10;
    },
    beforePagenation: (state) => {
      state.currentPage -= 10;
    }
  }
});

export const { nextPagenation, beforePagenation } = pagenationSlice.actions;
