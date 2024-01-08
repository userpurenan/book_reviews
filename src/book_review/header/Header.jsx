import React, { useEffect, useState } from 'react';
import { useCookies } from 'react-cookie';
import { useSelector, useDispatch } from 'react-redux';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { signOut } from '../../authSlice';
import { useUrl } from '../../useUrl';
import './header.scss';

export const Header = () => {
  const auth = useSelector((state) => state.auth.isSignIn);
  const dispatch = useDispatch();
  const [cookies, , removeCookie] = useCookies();
  const [user, setUsers] = useState('');
  const get_user_url = useUrl('user_operation'); //カスタムフック。このコンポーネントで使うapiのurlが返る

  const handleSignOut = () => {
    dispatch(signOut());
    removeCookie('token');
  };

  const headers = {
    authorization: `Bearer ${cookies.token}`
  };

  useEffect(() => {
    //ログインしていたらユーザー情報を取得する
    if (auth) {
      axios
        .get(get_user_url, { headers })
        .then((res) => {
          setUsers(res.data);
        })
        .catch((error) => {
          alert(`ヘッダーのユーザー情報の取得に失敗しました。${error}`);
        });
    }
  }, []);

  return (
    <header className="header">
      <Link to={'/'} className="Navigate-home">
        <h1>書籍レビュー</h1>
      </Link>
      {auth ? (
        <div className="userContainer">
          <Link to={'/new'} className="Navigate-button">
            書籍レビュー投稿画面へ
          </Link>
          <Link to={'/profile'} className="Navigate-button" state={user.name}>
            ユーザー名変更
          </Link>
          <Link to={'/login'} onClick={handleSignOut} className="Navigate-button">
            ログアウト
          </Link>
          <br />
          <p className="userName">
            ユーザー名：{user.name}
            <img src={user.image_url} alt="ユーザーのアイコン画像" className="userIcon" />
          </p>
        </div>
      ) : (
        <Link to={'/login'} className="Navigate-Login">
          ログイン
        </Link>
      )}
    </header>
  );
};
